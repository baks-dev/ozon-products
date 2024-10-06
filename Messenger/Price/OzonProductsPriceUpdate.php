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

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Lock\AppLockInterface;
use BaksDev\Ozon\Products\Api\Card\Price\Info\OzonPriceInfoDTO;
use BaksDev\Ozon\Products\Api\Card\Price\Info\OzonPriceInfoRequest;
use BaksDev\Ozon\Products\Api\Card\Price\Update\OzonPriceUpdateRequest;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Yandex\Market\Products\Api\Products\Card\YandexMarketProductDTO;
use BaksDev\Yandex\Market\Products\Api\Products\Card\FindProductYandexMarketRequest;
use BaksDev\Yandex\Market\Products\Api\Products\Price\YandexMarketProductPriceUpdateRequest;
use BaksDev\Yandex\Market\Products\Api\Tariffs\YandexMarketCalculatorRequest;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsStocks\YaMarketProductsStocksInterface;
use DateInterval;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\ItemInterface;

#[AsMessageHandler]
final class OzonProductsPriceUpdate
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly OzonPriceInfoRequest $ozonPriceInfoRequest,
        private readonly OzonPriceUpdateRequest $ozonPriceUpdateRequest,
        private readonly ProductsOzonCardInterface $productsOzonCard,
        private readonly AppLockInterface $appLock,
        private readonly AppCacheInterface $appCache,
        LoggerInterface $ozonProductsLogger,
    ) {
        $this->logger = $ozonProductsLogger;
    }

    /**
     * Обновляем базовую цену товара на Ozon
     */
    public function __invoke(OzonProductsPriceMessage $message): void
    {

        $Card = $this->productsOzonCard
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->find();

        if($Card === false)
        {
            return;
        }

        /** Не обновляем базовую стоимость карточки без цены */
        if(empty($Card['product_price']))
        {
            return;
        }

        if(
            empty($Card['width']) ||
            empty($Card['height']) ||
            empty($Card['length']) ||
            empty($Card['weight'])
        ) {
            $this->logger->critical(
                sprintf('Параметры упаковки товара %s не найдены! Не обновляем базовую стоимость товара Ozon', $Card['article']),
                [self::class.':'.__LINE__]
            );

            return;
        }

        /** Проверяем, что карточка добавлена */
        $OzonProduct = $this->ozonPriceInfoRequest
            ->profile($message->getProfile())
            ->article([$Card['article']])
            ->findAll();

        /** Если карточка новая - понадобится время на публикацию в маркетплейсе */
        if(!$OzonProduct->valid())
        {
            $this->logger->critical(
                sprintf('Не обновляем базовую стоимость товара %s: карточка товара в каталоге Ozon пока не доступна', $Card['article']),
                [self::class.':'.__LINE__]
            );

            throw new DomainException('Карточка товара Ozon не найдена');
        }

        $Money = new Money($Card['product_price'], true);
        $Currency = new Currency($Card['product_currency']);


        /** Лимит: 100 запросов в минуту, добавляем лок */
        $this->appLock
            ->createLock([$message->getProfile(), self::class])
            ->lifetime((60 / 100))
            ->waitAllTime();


        /** @var OzonPriceInfoDTO $OzonProductPriceInfo */
        $OzonProductPriceInfo = $OzonProduct->current();
        $Price = $OzonProductPriceInfo->getPrice();

        /** Обновляем базовую стоимость товара если цена изменилась */
        if(false === $OzonProductPriceInfo->getPrice()->equals($Money))
        {
            $this->ozonPriceUpdateRequest
                ->profile($message->getProfile())
                ->article($Card['article'])
                ->price($Price)
                ->currency($Currency)
                ->update();

            $this->logger->info(
                sprintf(
                    'Обновили стоимость товара %s (%s) : %s => %s %s',
                    $Card['article'],
                    $Money->getValue(), // стоимость в карточке
                    $OzonProductPriceInfo->getPrice()->getValue(), // предыдущая стоимость на маркетплейс
                    $Price->getValue(), // новая стоимость на маркетплейс
                    $Currency->getCurrencyValueUpper()
                ),
                [self::class.':'.__LINE__]
            );
        }
    }
}
