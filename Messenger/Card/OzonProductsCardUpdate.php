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

namespace BaksDev\Ozon\Products\Messenger\Card;

use BaksDev\Core\Deduplicator\DeduplicatorInterface;
use BaksDev\Core\Lock\AppLockInterface;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Orders\Order\Type\Status\OrderStatus\OrderStatusNew;
use BaksDev\Ozon\Products\Api\Card\Update\OzonCardUpdateRequest;
use BaksDev\Ozon\Products\Mapper\ItemOzonProducts;
use BaksDev\Ozon\Products\Mapper\Property\OzonProductsPropertyInterface;

use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class OzonProductsCardUpdate
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly ProductsOzonCardInterface $ozonProductsCard,
        private readonly OzonCardUpdateRequest $ozonCardUpdateRequest,
        private readonly ItemOzonProducts $itemOzonProducts,
        private readonly DeduplicatorInterface $deduplicator,
        LoggerInterface $ozonProductsLogger,
    ) {
        $this->logger = $ozonProductsLogger;
    }


    /** Добавляем (обновляем) карточку товара на Ozon*/

    public function __invoke(OzonProductsCardMessage $message): void
    {
        $product = $this->ozonProductsCard
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getOfferVariation())
            ->forModificationConst($message->getOfferModification())
            ->find();

        if(!$product)
        {
            return;
        }


        /** Не добавляем карточку без цены */

        if(!$product['product_price'])
        {
            $this->logger->warning(sprintf('Не добавляем карточку %s без цены', $product['article']));
            return;
        }


        $Card = $this->itemOzonProducts->getData($product);


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


        /** Выполняем запрос на создание/обновление карточки */
        $this->ozonCardUpdateRequest
            ->profile($message->getProfile())
            ->update($Card);

        $this->logger->info(sprintf('Обновили карточку товара %s', $Card['offer_id']));

        $Deduplicator->save();

    }
}
