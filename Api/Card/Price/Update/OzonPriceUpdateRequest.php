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

namespace BaksDev\Ozon\Products\Api\Card\Price\Update;

use App\Kernel;
use BaksDev\Ozon\Api\Ozon;
use BaksDev\Reference\Currency\Type\Currencies\RUR;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use DomainException;
use Generator;

/**
 * Обновновление цены
 * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ImportProductsPrices
 */
final class OzonPriceUpdateRequest extends Ozon
{
    private string $article;

    private Money $price;

    private Money $oldPrice;

    private Money|false $minPrice = false;

    private int|false $product = false;

    private Currency $currency;

    public function article(string $article): self
    {
        $this->article = $article;
        return $this;
    }

    public function price(Money $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function oldPrice(?Money $oldPrice): self
    {
        if (null === $oldPrice)
        {
            $this->oldPrice = new Money(0);
        }
        else
        {
            $this->oldPrice = $oldPrice;
        }

        return $this;
    }

    public function minPrice(?Money $minPrice): self
    {
        if (null === $minPrice)
        {
            $this->minPrice = false;
        }
        else
        {
            $this->minPrice = $minPrice;
        }

        return $this;
    }

    public function product(?int $product): self
    {
        if (null === $product)
        {
            $this->product = false;
        }
        else
        {
            $this->product = $product;
        }

        return $this;
    }

    public function currency(?Currency $currency): self
    {
        if(null === $currency)
        {
            $this->currency = new Currency(RUR::class);
        }
        else
        {
            $this->currency = $currency;
        }

        return $this;
    }


    public function update(): Generator
    {
        if (Kernel::isTestEnvironment())
        {
            return true;
        }

        $prices["auto_action_enabled"]      = "UNKNOWN";
        $prices["currency_code"]            = $this->currency->getCurrencyValueUpper();
        $prices["offer_id"]                 = $this->article;
        $prices["old_price"]                = (string)$this->oldPrice->getValue();
        $prices["price"]                    = (string)$this->price->getValue();
        $prices["price_strategy_enabled"]   = "UNKNOWN";

        if($this->minPrice)
        {
            $prices["min_price"]  = $this->minPrice;
        }

        if($this->product)
        {
            $prices["product_id"] = $this->product;
        }

        /**
         *
         * Пример запроса:
         *
         * "prices":
         *
         *      [
         *          {
         *              Атрибут для включения и выключения автоприменения акций
         *              Enum: "UNKNOWN" "ENABLED" "DISABLED", Default: "UNKNOWN"
         *              "auto_action_enabled": "UNKNOWN",
         *
         *              Валюта ваших цен
         *              "currency_code": "RUB",
         *
         *              Минимальная цена товара после применения акций.
         *              "min_price": "800",
         *
         *              Идентификатор товара в системе продавца.
         *              "offer_id": "",
         *
         *              Цена до скидок (зачеркнута на карточке товара).
         *              "old_price": "0",
         *
         *              Цена товара с учётом скидок, отображается на карточке товара.
         *              "price": "1448",
         *
         *              Атрибут для автоприменения стратегий цены:
         *              Enum: "UNKNOWN" "ENABLED" "DISABLED", Default: "UNKNOWN"
         *              "price_strategy_enabled": "UNKNOWN",
         *
         *              Идентификатор товара
         *              "product_id": 1386
         *          }
         *      ]
         *
         */
        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                '/v1/product/import/prices',
                [
                    "json" => [
                        'prices' => $prices
                    ]
                ]
            );

        $content = $response->toArray(false);

        if ($response->getStatusCode() !== 200)
        {

            $this->logger->critical($content['code'] . ': ' . $content['message'], [self::class . ':' . __LINE__]);


            throw new DomainException(
                message: 'Ошибка ' . self::class,
                code: $response->getStatusCode()
            );
        }

        foreach ($content['result'] as $item)
        {
            yield new OzonPriceUpdateDTO($item);
        }
    }
}
