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

namespace BaksDev\Ozon\Products\Messenger\Stocks;

use BaksDev\Core\Deduplicator\DeduplicatorInterface;
use BaksDev\Core\Lock\AppLockInterface;
use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonProductStockDTO;
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonStockInfoDTO;
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonStockInfoRequest;
use BaksDev\Ozon\Products\Api\Card\Stocks\Update\OzonStockUpdateDTO;
use BaksDev\Ozon\Products\Api\Card\Stocks\Update\OzonStockUpdateRequest;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class OzonProductsStocksUpdate
{
    public function __construct(
        #[Target('ozonProductsLogger')] private LoggerInterface $logger,
        private OzonStockUpdateRequest $ozonStockUpdateRequest,
        private OzonStockInfoRequest $ozonProductStocksInfoRequest,
        private ProductsOzonCardInterface $ozonProductsCard,
        private DeduplicatorInterface $deduplicator,
        private MessageDispatchInterface $messageDispatch,
    ) {}

    /**
     * Обновляем остатки товаров Ozon
     */
    public function __invoke(OzonProductsStocksMessage $message): void
    {
        $Card = $this->ozonProductsCard
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->find();

        if($Card === false)
        {
            return;
        }

        /** Не обновляем остатки карточки без цены */
        if(empty($Card['product_price']))
        {
            return;
        }

        /** Получаем информацию о количестве товаров */
        $ProductStocksInfo = $this->ozonProductStocksInfoRequest
            ->profile($message->getProfile())
            ->article([$Card['article']])
            ->findAll();

        if(false === $ProductStocksInfo || $ProductStocksInfo->valid() === false)
        {
            return;
        }

        /** @var  OzonStockInfoDTO $ProductStocks */
        $ProductStocks = $ProductStocksInfo->current();
        $productStockQuantity = 0;

        /** @var OzonProductStockDTO $stock */
        foreach($ProductStocks->getStocks() as $stock)
        {
            if($stock->getType() === 'fbs')
            {
                /** Остатки всегда одинаковы для всех */
                $productStockQuantity = $stock->getPresent();
                $productStockQuantity = max($productStockQuantity, 0);

                break;
            }
        }

        /**
         * Сверяем, что остатки на маркетплейс равны остаткам в системе
         */
        $product_quantity = isset($Card['product_quantity']) ? max($Card['product_quantity'], 0) : 0;

        if($productStockQuantity === $product_quantity)
        {
            $this->logger->info('{article}: Наличие соответствует ({old} == {new})', [
                'article' => $Card['article'],
                'old' => $productStockQuantity,
                'new' => $product_quantity,
                'profile' => $message->getProfile()
            ]);

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
            /** Пробуем обновится через 2 минуты */
            $this->messageDispatch->dispatch(
                message: $message,
                stamps: [new MessageDelay('1 minutes')], // задержка 1 минуту для обновления карточки
                transport: 'ozon-products-low'
            );

            return;
        }

        /** Обновляем остатки товара если наличие изменилось */
        $result = $this->ozonStockUpdateRequest
            ->profile($message->getProfile())
            ->article($Card['article'])
            ->total($product_quantity)
            ->update();


        /** @var OzonStockUpdateDTO $OzonStockUpdateDTO */
        $OzonStockUpdateDTO = $result->current();

        if(false === $OzonStockUpdateDTO->updated())
        {
            /** Пробуем обновится через 2 минуты */
            $this->messageDispatch->dispatch(
                message: $message,
                stamps: [new MessageDelay('1 minutes')], // задержка 2 минуты для обновления карточки
                transport: 'ozon-products-low'
            );

            return;
        }

        $this->logger->info('Обновили наличие {article}: {old} => {new}', [
            'article' => $Card['article'],
            'old' => $productStockQuantity,
            'new' => $product_quantity,
            'profile' => $message->getProfile()
        ]);

        $Deduplicator->save();
    }
}
