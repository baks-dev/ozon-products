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
use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Orders\Order\Repository\ProductTotalInOrders\ProductTotalInOrdersInterface;
use BaksDev\Ozon\Products\Api\Card\Stocks\Update\OzonStockUpdateDTO;
use BaksDev\Ozon\Products\Api\Card\Stocks\Update\OzonStockUpdateRequest;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use BaksDev\Ozon\Repository\OzonTokensByProfile\OzonTokensByProfileInterface;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обновляем остатки товаров Ozon
 */
#[AsMessageHandler]
#[Autoconfigure(shared: false)]
final readonly class OzonProductsStocksUpdateDispatcher
{
    public function __construct(
        #[Target('ozonProductsLogger')] private LoggerInterface $logger,
        private OzonStockUpdateRequest $ozonStockUpdateRequest,
        private ProductsOzonCardInterface $OzonProductsCardRepository,
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

        $ProductsOzonCardResult = $this->OzonProductsCardRepository
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->forProfile($message->getProfile())
            ->find();

        if(false === ($ProductsOzonCardResult instanceof ProductsOzonCardResult))
        {
            $this->logger->critical(
                sprintf('ozon-products: Ошибка при обновлении остатков! Карточка товара артикула %s не найдена', $ProductsOzonCardResult->getArticle()),
                [var_export($message, true), self::class.':'.__LINE__],
            );

            return;
        }

        /** Не обновляем остатки карточки без цены */
        if(empty($ProductsOzonCardResult->getProductPrice()?->getRoundValue()))
        {
            $this->logger->critical(
                sprintf('ozon-products: Ошибка при обновлении остатков! Стоимость артикула %s не найдена', $ProductsOzonCardResult->getArticle()),
                [var_export($message, true), self::class.':'.__LINE__],
            );

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
            $this->logger->critical(
                sprintf('ozon-products: Ошибка при обновлении остатков! Параметры карточки артикула %s не найдены', $ProductsOzonCardResult->getArticle()),
                [var_export($message, true), self::class.':'.__LINE__],
            );

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

        $ProductQuantity = max($ProductQuantity, 0);

        foreach($tokensByProfile as $OzonTokenUid)
        {
            $Deduplicator = $this->deduplicator
                ->namespace('ozon-products')
                ->expiresAfter('1 minutes')
                ->deduplication([
                    $ProductsOzonCardResult->getArticle(),
                    (string) $OzonTokenUid,
                    $ProductQuantity,
                    self::class,
                ]);

            if($Deduplicator->isExecuted())
            {
                $this->logger->warning(
                    sprintf('%s: Пропустили обновление остатков => {new}', $ProductsOzonCardResult->getArticle()),
                    [
                        'new' => $ProductQuantity,
                        (string) $OzonTokenUid,
                        self::class.':'.__LINE__,
                    ]);

                $Deduplicator->save();

                continue;
            }

            $Deduplicator->save();

            /**
             * Обновляем остатки товара
             */

            $result = $this->ozonStockUpdateRequest
                ->forTokenIdentifier($OzonTokenUid)
                ->article($ProductsOzonCardResult->getArticle())
                ->total($ProductQuantity)
                ->update();

            if(is_null($result))
            {
                /** Пробуем обновится позже */

                $this->logger->warning('{article}: пробуем обновить остаток позже => {new}', [
                    'article' => $ProductsOzonCardResult->getArticle(),
                    'token' => (string) $OzonTokenUid,
                    'new' => $ProductQuantity,
                    self::class.':'.__LINE__,
                ]);

                $this->messageDispatch->dispatch(
                    message: $message,
                    stamps: [new MessageDelay('1 minutes')],
                    transport: $message->getProfile().'-low',
                );

                $Deduplicator->delete();

                continue;
            }


            if(false === $result)
            {
                $this->logger->info('{article}: Продукт с артикулом на маркетплейcе не найден', [
                    'article' => $ProductsOzonCardResult->getArticle(),
                    'token' => (string) $OzonTokenUid,
                    self::class.':'.__LINE__,
                ]);

                continue;
            }

            /** TRUE возвращается если токен не предполагает обновление остатков (либо обнуляет) */
            if(true === $result || false === $result->valid())
            {
                $this->logger->info('{article}: Остановили продажу товара => {new}', [
                    'article' => $ProductsOzonCardResult->getArticle(),
                    'token' => (string) $OzonTokenUid,
                    'new' => $ProductQuantity,
                    self::class.':'.__LINE__,
                ]);

                continue;
            }

            /** @var OzonStockUpdateDTO $OzonStockUpdateDTO */
            $OzonStockUpdateDTO = $result->current();

            if(false === $OzonStockUpdateDTO->updated())
            {
                /** Пробуем обновится позже */

                $this->logger->warning('{article}: пробуем обновить остаток позже => {new}', [
                    'article' => $ProductsOzonCardResult->getArticle(),
                    'token' => (string) $OzonTokenUid,
                    'new' => $ProductQuantity,
                    self::class.':'.__LINE__,
                ]);

                /** Пробуем обновится позже */
                $this->messageDispatch->dispatch(
                    message: $message,
                    stamps: [new MessageDelay('1 minutes')],
                    transport: $message->getProfile().'-low',
                );

                $Deduplicator->delete();

                continue;
            }

            $this->logger->info('{article}: Обновили наличие => {new}', [
                'article' => $ProductsOzonCardResult->getArticle(),
                'new' => $ProductQuantity,
                'token' => (string) $OzonTokenUid,
                self::class.':'.__LINE__,
            ]);

        }
    }
}
