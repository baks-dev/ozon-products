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

namespace BaksDev\Ozon\Products\Api\Card\Price;

use BaksDev\Ozon\Api\Ozon;
use BaksDev\Reference\Money\Type\Money;
use DomainException;
use Generator;

final class UpdateOzonProductPriceRequest extends Ozon
{
    private string $article;

    private int $total = 0;

    private int $warehouse;

    private int|false $product = false;

    private Money $price;

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

    /**
     * Обновить количество товаров на складах
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ImportProductsStocks
     */
    public function update(): bool
    {
        /**
         * Выполнять операции запроса ТОЛЬКО в PROD окружении
         */
        if($this->isExecuteEnvironment() === false)
        {
            return true;
        }


        /**
         * Формируем массив с ключами для отправки JSON
         * Пример запроса:
         *  "prices": [
         *       {
         *         "auto_action_enabled": "UNKNOWN",
         *         "currency_code": "RUB",
         *         "min_price": "800",
         *              "offer_id": "",
         *              "old_price": "0",
         *              "price": "1448",
         *              "price_strategy_enabled": "UNKNOWN",
         *              "product_id": 1386
         *       }
         *   ]
         */


        $prices['auto_action_enabled'] = 'UNKNOWN';
        $prices["offer_id"] = $this->article;
        $prices["price"] = $this->price->getValue();
        $prices['min_price'] = $this->price->getValue();
        $prices['old_price'] = $this->price->getValue();
        $prices['currency_code'] = 'RUB';
        $prices['price_strategy_enabled'] = 'UNKNOWN';


        //        if($this->product)
        //        {
        //            $stocks["product_id"] = $this->product;
        //        }


        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                '/v1/product/import/prices',
                [
                    "json" => [
                        'prices' => [$prices]
                    ]
                ]
            );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            $this->logger->critical($content['code'].': '.$content['message'], [self::class.':'.__LINE__]);

            throw new DomainException(
                message: 'Ошибка '.self::class,
                code: $response->getStatusCode()
            );
        }

        $result = current($content['result']);

        return (bool) $result['updated'];
    }
}
