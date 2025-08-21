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

namespace BaksDev\Ozon\Products\Messenger\Card;

use BaksDev\Core\Deduplicator\DeduplicatorInterface;
use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Products\Api\Card\Price\GetOzonProductCalculatorRequest;
use BaksDev\Ozon\Products\Api\Card\Update\UpdateOzonCardRequest;
use BaksDev\Ozon\Products\Mapper\OzonProductsMapper;
use BaksDev\Ozon\Products\Messenger\Card\Result\ResultOzonProductsCardMessage;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use BaksDev\Ozon\Repository\OzonTokensByProfile\OzonTokensByProfileInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Добавляем (обновляем) карточку товара на Ozon
 */
#[AsMessageHandler]
final readonly class OzonProductsCardUpdateDispatcher
{
    public function __construct(
        #[Target('ozonProductsLogger')] private LoggerInterface $logger,
        private GetOzonProductCalculatorRequest $GetOzonProductCalculatorRequest,
        private ProductsOzonCardInterface $ozonProductsCard,
        private UpdateOzonCardRequest $ozonCardUpdateRequest,
        private OzonProductsMapper $OzonProductsMapper,
        private DeduplicatorInterface $deduplicator,
        private MessageDispatchInterface $messageDispatch,
        private OzonTokensByProfileInterface $OzonTokensByProfile,
    ) {}

    /**
     * Добавляем (обновляем) карточку товара на Ozon
     */
    public function __invoke(OzonProductsCardMessage $message): void
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

        /** Не добавляем карточку без наличия */
        if(empty($ProductsOzonCardResult->getProductQuantity()))
        {
            $this->logger->warning(sprintf('Не добавляем карточку %s без наличия', $ProductsOzonCardResult->getArticle()));
            return;
        }


        /** Не добавляем карточку без цены */
        if(empty($ProductsOzonCardResult->getProductPrice()?->getRoundValue()))
        {
            $this->logger->warning(sprintf('Не добавляем карточку %s без цены', $ProductsOzonCardResult->getArticle()));
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


        /** Гидрируем карточку на свойства запроса */

        $request = $this->OzonProductsMapper
            ->getData($ProductsOzonCardResult);

        foreach($tokensByProfile as $OzonTokenUid)
        {
            /** Лимит: 1 карточка 1 раз в 2 минуты */
            $Deduplicator = $this->deduplicator
                ->namespace('ozon-products')
                ->expiresAfter('5 seconds')
                ->deduplication([
                    (string) $message->getProduct(),
                    (string) $message->getOfferConst(),
                    (string) $message->getVariationConst(),
                    (string) $message->getModificationConst(),
                    (string) $OzonTokenUid,
                    self::class,
                ]);

            if($Deduplicator->isExecuted())
            {
                continue;
            }

            $Deduplicator->save();

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


            $request['price'] = (string) $Money->getRoundValue();

            $oldPrice = clone $Money;
            $oldPrice->applyString('6%');
            $request['old_price'] = (string) $oldPrice->getRoundValue();

            /**
             * Выполняем запрос на создание/обновление карточки
             */

            $task = $this->ozonCardUpdateRequest
                ->forTokenIdentifier($OzonTokenUid)
                ->update($request);

            if($task === false)
            {
                /**
                 * Ошибка запишется в лог
                 *
                 * @see UpdateOzonCardRequest
                 */
                return;
            }

            $this->logger->info(
                sprintf('Обновили карточку товара %s', $ProductsOzonCardResult->getArticle()),
                [(string) $OzonTokenUid],
            );


            /**
             * Запускаем процесс проверки задания
             */

            $ResultOzonProductsCardUpdateMessage = new ResultOzonProductsCardMessage(
                $task,
                $message->getProfile(),
            );

            $this->messageDispatch->dispatch(
                message: $ResultOzonProductsCardUpdateMessage,
                transport: 'ozon-products-low',
            );
        }
    }
}
