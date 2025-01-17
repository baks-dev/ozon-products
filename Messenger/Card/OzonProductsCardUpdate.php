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
use BaksDev\Ozon\Products\Messenger\Price\OzonProductsPriceMessage;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Ozon\Promotion\BaksDevOzonPromotionBundle;
use BaksDev\Reference\Money\Type\Money;
use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class OzonProductsCardUpdate
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly GetOzonProductCalculatorRequest $GetOzonProductCalculatorRequest,
        private readonly ProductsOzonCardInterface $ozonProductsCard,
        private readonly UpdateOzonCardRequest $ozonCardUpdateRequest,
        private readonly OzonProductsMapper $itemOzonProducts,
        private readonly DeduplicatorInterface $deduplicator,
        private readonly MessageDispatchInterface $messageDispatch,
        LoggerInterface $ozonProductsLogger,
    )
    {
        $this->logger = $ozonProductsLogger;
    }


    /**
     * Добавляем (обновляем) карточку товара на Ozon
     */
    public function __invoke(OzonProductsCardMessage $message): void
    {
        $product = $this->ozonProductsCard
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getOfferVariation())
            ->forModificationConst($message->getOfferModification())
            ->find();

        if(false === $product)
        {
            return;
        }

        /** Не добавляем карточку без цены */

        if(!$product['product_price'])
        {
            $this->logger->warning(sprintf('Не добавляем карточку %s без цены', $product['article']));
            return;
        }

        /** Присваиваем профиль пользователя для всех Request запросов  */
        $this->ozonCardUpdateRequest->profile($message->getProfile());

        $Card = $this->itemOzonProducts->getData($product);


        /** Не обновляем карточку без параметров упаковки */
        if(
            empty($Card['width']) ||
            empty($Card['height']) ||
            empty($Card['depth']) ||
            empty($Card['weight'])
        )
        {
            return;
        }

        /** Лимит: 1 карточка 1 раз в 2 минуты */
        $Deduplicator = $this->deduplicator
            ->namespace('ozon-products')
            ->expiresAfter(DateInterval::createFromDateString('2 minutes'))
            ->deduplication([
                $message,
                self::class
            ]);

        if($Deduplicator->isExecuted())
        {
            $this->logger->critical(
                sprintf('ozon-products: Отложили обновление карточки %s на 2 минуты', $Card['offer_id']),
                [self::class.''.__LINE__]
            );

            /** Добавляем отложенное обновление */
            $this->messageDispatch->dispatch(
                message: $message,
                stamps: [new MessageDelay('2 minutes')], // задержка 2 минуты для обновления карточки
                transport: 'ozon-products-low'
            );

            return;
        }


        /** Получаем стоимость услуг и присваиваем полную стоимость */

        $Money = $this->GetOzonProductCalculatorRequest
            ->category($Card['description_category_id'])
            ->width($Card['width'] / 10)
            ->height($Card['height'] / 10)
            ->length($Card['depth'] / 10)
            ->weight($Card['weight'] / 1000)
            ->price(new Money($Card['price']))
            ->calc();


        /** Если произошла временная ошибка калькулятора - пробуем позже */
        if($Money === false)
        {
            $this->messageDispatch->dispatch(
                message: $message,
                stamps: [new MessageDelay('5 seconds')], // отложенная на 5 секунд
                transport: 'ozon-products'
            );

            return;
        }

        $Card['price'] = $Money->getValue();


        /**
         * Если установлен модуль ozon-promotion - обновляем цену с учетом скидок
         */

        if(class_exists(BaksDevOzonPromotionBundle::class))
        {
            $this->messageDispatch->dispatch(message: new OzonProductsPriceMessage($message));
        }


        /** Выполняем запрос на создание/обновление карточки */

        $task = $this->ozonCardUpdateRequest->update($Card);

        if($task === false)
        {
            $this->logger->critical(sprintf('ozon-products: Ошибка при попытке обновить карточку товара %s', $Card['offer_id']));
            return;
        }

        $this->logger->info(sprintf('Обновили карточку товара %s', $Card['offer_id']));
        $Deduplicator->save();

        /**
         * Запускаем процесс проверки задания
         */

        $ResultOzonProductsCardUpdateMessage = new ResultOzonProductsCardMessage(
            $task,
            $message->getProfile()
        );

        $this->messageDispatch->dispatch(
            message: $ResultOzonProductsCardUpdateMessage,
            stamps: [new MessageDelay('5 seconds')], // отложенная на 5 секунд
            transport: 'ozon-products'
        );

    }
}
