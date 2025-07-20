<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Messenger\Price;

use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Products\Api\Card\Price\GetOzonProductCalculatorRequest;
use BaksDev\Ozon\Products\Api\Card\Price\UpdateOzonProductPriceRequest;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use BaksDev\Ozon\Repository\OzonTokensByProfile\OzonTokensByProfileInterface;
use BaksDev\Reference\Money\Type\Money;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class OzonProductsPriceUpdateDispatcher
{
    public function __construct(
        #[Target('ozonProductsLogger')] private LoggerInterface $logger,
        private UpdateOzonProductPriceRequest $updateOzonProductPriceRequest,
        private ProductsOzonCardInterface $ozonProductsCard,
        private MessageDispatchInterface $messageDispatch,
        private GetOzonProductCalculatorRequest $GetOzonProductCalculatorRequest,
        private OzonTokensByProfileInterface $OzonTokensByProfile
    ) {}

    /**
     * Обновляем стоимость товаров Ozon
     */
    public function __invoke(OzonProductsPriceMessage $message): void
    {
        /** Получаем все токены профиля */

        $tokensByProfile = $this->OzonTokensByProfile
            ->onlyCardUpdate() // только обновляющие карточки
            ->findAll($message->getProfile());

        if(false === $tokensByProfile || false === $tokensByProfile->valid())
        {
            return;
        }

        $ProductsOzonCardResult = $this->ozonProductsCard
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->forProfile($message->getProfile())
            ->find();

        if(false === ($ProductsOzonCardResult instanceof ProductsOzonCardResult))
        {
            return;
        }

        /** Не обновляем стоимость карточки без цены */
        if(empty($ProductsOzonCardResult->getProductPrice()?->getRoundValue()))
        {
            return;
        }

        /** Не обновляем карточку без параметров упаковки */
        if(
            empty($ProductsOzonCardResult->getWidth())
            || empty($ProductsOzonCardResult->getHeight())
            || empty($ProductsOzonCardResult->getLength())
            || empty($ProductsOzonCardResult->getWeight())
        )
        {
            $this->logger->warning(sprintf('Не добавляем карточку %s без параметров упаковки', $ProductsOzonCardResult->getArticle()));
            return;
        }

        foreach($tokensByProfile as $OzonTokenUid)
        {
            /**
             * Получаем стоимость услуг и присваиваем полную стоимость
             * Переменная $Money = стоимость товара + стоимость услуг
             */

            $Money = $this->GetOzonProductCalculatorRequest
                ->forTokenIdentifier($OzonTokenUid)
                ->width($ProductsOzonCardResult->getWidth())
                ->height($ProductsOzonCardResult->getHeight())
                ->length($ProductsOzonCardResult->getLength())
                ->weight($ProductsOzonCardResult->getWeight())
                ->price($ProductsOzonCardResult->getProductPrice())
                ->calc();

            if(false === ($Money instanceof Money))
            {
                $this->logger->critical(
                    sprintf('ozon-products: Ошибка при расчете стоимости услуг артикула %s', $ProductsOzonCardResult->getArticle()),
                    ['token' => (string) $OzonTokenUid],
                );

                continue;
            }


            /** Обновляем стоимость */

            $result = $this->updateOzonProductPriceRequest
                ->forTokenIdentifier($OzonTokenUid)
                ->price($Money)
                ->article($ProductsOzonCardResult->getArticle())
                ->update();


            if($result === false)
            {
                /* Пробуем обновить стоимость через 1 минуту */
                $this->messageDispatch->dispatch(
                    message: $message,
                    stamps: [new MessageDelay('1 minute')],
                    transport: 'ozon-products-low',
                );

                return;
            }

            $this->logger->info(
                sprintf('Обновили стоимость %s => %s', $ProductsOzonCardResult->getArticle(), $Money->getRoundValue()),
                ['token' => (string) $OzonTokenUid],
            );

        }
    }
}
