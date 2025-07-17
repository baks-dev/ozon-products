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
use BaksDev\Ozon\Products\Mapper\OzonProductsMapper;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Reference\Money\Type\Money;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class OzonProductsPriceUpdate
{
    public function __construct(
        #[Target('ozonProductsLogger')] private LoggerInterface $logger,
        private UpdateOzonProductPriceRequest $updateOzonProductPriceRequest,
        private OzonProductsMapper $itemOzonProducts,
        private ProductsOzonCardInterface $ozonProductsCard,
        private MessageDispatchInterface $messageDispatch,
        private GetOzonProductCalculatorRequest $GetOzonProductCalculatorRequest,
    ) {}

    /**
     * Обновляем стоимость товаров Ozon
     */
    public function __invoke(OzonProductsPriceMessage $message): void
    {
        $product = $this->ozonProductsCard
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->find();

        if($product === false)
        {
            return;
        }

        /** Не обновляем стоимость карточки без цены */
        if(empty($product['product_price']))
        {
            return;
        }

        /** Присваиваем профиль пользователя для всех Request запросов Items */
        $this->updateOzonProductPriceRequest->profile($message->getProfile());
        $Card = $this->itemOzonProducts->getData($product);


        /** Не обновляем стоимость без параметров упаковки */
        if(
            empty($Card['width']) ||
            empty($Card['height']) ||
            empty($Card['depth']) ||
            empty($Card['weight'])
        )
        {
            return;
        }

        /**
         * Получаем стоимость услуг и присваиваем полную стоимость
         * Важно! При пересчете стоимости присваивается торговая наценка
         */

        $price = $this->GetOzonProductCalculatorRequest
            ->profile($message->getProfile())
            ->width($Card['width'] / 10)
            ->height($Card['height'] / 10)
            ->length($Card['depth'] / 10)
            ->weight($Card['weight'] / 1000)
            ->price(new Money($Card['price']))
            ->calc();


        /** Обновляем стоимость */

        $result = $this->updateOzonProductPriceRequest
            ->profile($message->getProfile())
            ->price($price)
            ->article($Card['offer_id'])
            ->update();

        if($result === false)
        {
            /* Пробуем обновить стоимость через 1 минуту */
            $this->messageDispatch->dispatch(
                message: $message,
                stamps: [new MessageDelay('1 minute')],
                transport: 'ozon-products-low'
            );

            return;
        }

        $this->logger->info(
            sprintf('Обновили стоимость %s => %s', $Card['offer_id'], $price->getValue()),
            ['profile' => (string) $message->getProfile()]
        );

    }
}
