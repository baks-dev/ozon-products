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

namespace BaksDev\Ozon\Products\Messenger\Price;

use BaksDev\Core\Deduplicator\DeduplicatorInterface;
use BaksDev\Core\Lock\AppLockInterface;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Products\Api\Card\Price\UpdateOzonProductPriceRequest;
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonProductStockDTO;
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonStockInfoDTO;
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonStockInfoRequest;
use BaksDev\Ozon\Products\Api\Card\Stocks\Update\OzonStockUpdateDTO;
use BaksDev\Ozon\Products\Api\Card\Stocks\Update\OzonStockUpdateRequest;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Reference\Money\Type\Money;
use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final class OzonProductsPriceUpdate
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly UpdateOzonProductPriceRequest $updateOzonProductPriceRequest,
        private readonly OzonStockInfoRequest $ozonProductStocksInfoRequest,
        private readonly ProductsOzonCardInterface $ozonProductsCard,
        private readonly DeduplicatorInterface $deduplicator,
        private readonly AppLockInterface $appLock,
        private readonly MessageDispatchInterface $messageDispatch,
        LoggerInterface $ozonProductsLogger,
    ) {
        $this->logger = $ozonProductsLogger;
    }

    /**
     * Обновляем остатки товаров Ozon
     */
    public function __invoke(OzonProductsPriceMessage $message): void
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

        $price = new Money($Card['product_price'], true);
        $percent = $price->percent(15);
        $price->add($percent);

        $result = $this->updateOzonProductPriceRequest
            ->profile($message->getProfile())
            ->price($price)
            ->article($Card['article'])
            ->update();

        if($result === false)
        {
            return;
        }

        $this->logger->info('Обновили стоимость {article}: {rice}', [
            'article' => $Card['article'],
            'rice' => $price->getValue(),
            'profile' => $message->getProfile()
        ]);

    }
}
