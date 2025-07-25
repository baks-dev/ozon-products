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
use BaksDev\Orders\Order\Repository\ProductTotalInOrders\ProductTotalInOrdersInterface;
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonProductStockDTO;
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonStockInfoDTO;
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonStockInfoRequest;
use BaksDev\Ozon\Products\Api\Card\Stocks\Update\OzonStockUpdateDTO;
use BaksDev\Ozon\Products\Api\Card\Stocks\Update\OzonStockUpdateRequest;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Ozon\Repository\OzonTokensByProfile\OzonTokensByProfileInterface;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обновляем остатки товаров Ozon
 */
#[AsMessageHandler]
final readonly class OzonProductsStocksUpdateDispatcher
{
    public function __construct(
        #[Target('ozonProductsLogger')] private LoggerInterface $logger,
        private OzonStockUpdateRequest $ozonStockUpdateRequest,
        private OzonStockInfoRequest $ozonProductStocksInfoRequest,
        private ProductsOzonCardInterface $ozonProductsCard,
        private DeduplicatorInterface $deduplicator,
        private MessageDispatchInterface $messageDispatch,
        private OzonTokensByProfileInterface $OzonTokensByProfile,
        private ProductTotalInOrdersInterface $ProductTotalInOrders,

    ) {}

    /**
     * Обновляем остатки товаров Ozon
     */
    public function __invoke(OzonProductsStocksMessage $message): void
    {
        /** Получаем все токены профиля */

        $tokensByProfile = $this->OzonTokensByProfile->findAll($message->getProfile());

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

        if($ProductsOzonCardResult === false)
        {
            return;
        }

        /** Не обновляем остатки карточки без цены */
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
            return;
        }

        /**  Остаток товара в карточке либо если подключен модуль складского учета - остаток на складе */
        $ProductQuantity = $ProductsOzonCardResult->getProductQuantity();

        /** Если подключен модуль складского учета - расчет согласно необработанных заказов */
        if(class_exists(BaksDevProductsStocksBundle::class))
        {
            /** Получаем количество необработанных заказов */
            $unprocessed = $this->ProductTotalInOrders
                ->onProfile($message->getProfile())
                ->onProduct($message->getProduct())
                ->onOfferConst($message->getOfferConst())
                ->onVariationConst($message->getVariationConst())
                ->onModificationConst($message->getModificationConst())
                ->findTotal();

            $ProductQuantity -= $unprocessed;
        }


        foreach($tokensByProfile as $OzonTokenUid)
        {
            /** Получаем информацию о количестве товаров */
            $ProductStocksInfo = $this->ozonProductStocksInfoRequest
                ->forTokenIdentifier($OzonTokenUid)
                ->article($ProductsOzonCardResult->getArticle())
                ->find();

            $productStockQuantity = ($ProductStocksInfo instanceof OzonStockInfoDTO) ? $ProductStocksInfo->getTotal() : 0;

            /**
             * Сверяем, что остатки на маркетплейс равны остаткам в системе
             */

            if($productStockQuantity === $ProductQuantity)
            {
                $this->logger->info('{article}: Наличие соответствует ({old} == {new})', [
                    'article' => $ProductsOzonCardResult->getArticle(),
                    'old' => $productStockQuantity,
                    'new' => $ProductQuantity,
                    'profile' => $message->getProfile(),
                ]);

                continue;
            }

            /** Лимит: 1 карточка 1 раз в 2 минуты */
            $Deduplicator = $this->deduplicator
                ->namespace('ozon-products')
                ->expiresAfter(DateInterval::createFromDateString('2 minutes'))
                ->deduplication([
                    $message,
                    $OzonTokenUid,
                    self::class,
                ]);

            if($Deduplicator->isExecuted())
            {
                /** Пробуем обновится через 2 минуты */
                $this->messageDispatch->dispatch(
                    message: $message,
                    stamps: [new MessageDelay('1 minutes')], // задержка 1 минуту для обновления карточки
                    transport: 'ozon-products-low',
                );

                return;
            }

            /** Обновляем остатки товара если наличие изменилось */
            $result = $this->ozonStockUpdateRequest
                ->forTokenIdentifier($OzonTokenUid)
                ->article($ProductsOzonCardResult->getArticle())
                ->total($ProductQuantity)
                ->update();


            /** TRUE возвращается если токен не предполагает обновление остатков */
            if(true === $result || false === $result->valid())
            {
                return;
            }

            /** @var OzonStockUpdateDTO $OzonStockUpdateDTO */
            $OzonStockUpdateDTO = $result->current();

            if(false === $OzonStockUpdateDTO->updated())
            {
                /** Пробуем обновится через 2 минуты */
                $this->messageDispatch->dispatch(
                    message: $message,
                    stamps: [new MessageDelay('1 minutes')], // задержка 2 минуты для обновления карточки
                    transport: 'ozon-products-low',
                );

                return;
            }

            $this->logger->info('Обновили наличие {article}: {old} => {new}', [
                'article' => $ProductsOzonCardResult->getArticle(),
                'old' => $productStockQuantity,
                'new' => $ProductsOzonCardResult->getProductQuantity(),
                'profile' => $message->getProfile(),
            ]);

            $Deduplicator->save();
        }
    }
}
