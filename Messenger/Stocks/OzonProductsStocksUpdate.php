<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonProductStockDTO;
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonStockInfoDTO;
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonStockInfoRequest;
use BaksDev\Ozon\Products\Api\Card\Stocks\Update\OzonStockUpdateRequest;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class OzonProductsStocksUpdate
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly OzonStockUpdateRequest $ozonStockUpdateRequest,
        private readonly OzonStockInfoRequest $ozonProductStocksInfoRequest,
        private readonly ProductsOzonCardInterface $ozonProductsCard,
        private readonly DeduplicatorInterface $deduplicator,
        private readonly AppLockInterface $appLock,
        LoggerInterface $ozonProductsLogger,
    ) {
        $this->logger = $ozonProductsLogger;
    }

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

        /** Добавляем лок на процесс, остатки обновляются в порядке очереди! */
        $lock = $this->appLock
            ->createLock([$message->getProfile(), $Card['article'], self::class])
            ->waitAllTime();

        $ProductStocksInfo = $this->ozonProductStocksInfoRequest
            ->profile($message->getProfile())
            ->article([$Card['article']])
            ->findAll();

        $product_quantity = isset($Card['product_quantity']) ? max($Card['product_quantity'], 0) : 0;

        /** @var  OzonStockInfoDTO $ProductStocks */
        $ProductStocks = $ProductStocksInfo->current();
        $productStockQuantity = 0;

        /** @var OzonProductStockDTO $stock */
        foreach ($ProductStocks->getStocks() as $stock)
        {
            if($stock->getType() === 'fbs')
            {
                $productStockQuantity = $stock->getPresent();
            }
        }

        if($productStockQuantity === $product_quantity)
        {
            $this->logger->info(sprintf(
                'Наличие соответствует %s: %s == %s',
                $Card['article'],
                $productStockQuantity,
                $product_quantity
            ), [$message->getProfile()]);

            $lock->release();

            return;
        }

        /** Лимит: 1 карточка 1 раз в 2 минуты */
        $Deduplicator = $this->deduplicator
            ->namespace('ozon-products')
            ->deduplication([
                $Card['offer_id'],
                $message->getProfile(),
                md5(self::class)
            ]);

        $this->deduplicator->expiresAfter(DateInterval::createFromDateString('2 minutes'));

        if($Deduplicator->isExecuted())
        {
            return;
        }

        /** Обновляем остатки товара если наличие изменилось */
        $this->ozonStockUpdateRequest
            ->profile($message->getProfile())
            ->article($Card['article'])
            ->total($product_quantity)
            ->update();

        $this->logger->info(sprintf(
            'Обновили наличие %s: %s => %s',
            $Card['article'],
            $productStockQuantity,
            $product_quantity
        ), [$message->getProfile()]);


        $lock->release();

        $Deduplicator->save();
    }
}