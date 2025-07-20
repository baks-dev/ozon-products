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

namespace BaksDev\Ozon\Products\Api\Card\Stocks\Info;

use BaksDev\Ozon\Api\Ozon;
use Generator;

final class OzonStockInfoRequest extends Ozon
{
    private array $article;

    private array|false $product = false;

    public function article(array|string $article): self
    {
        is_array($article) ?: $article = [$article];

        $this->article = $article;
        return $this;
    }

    public function product(?array $product): self
    {
        if(null === $product)
        {
            $this->product = false;
        }
        else
        {
            $this->product = $product;
        }

        return $this;
    }

    /**
     * Информация об остатках на складах продавца (FBS и rFBS)
     *
     * @see https://api-seller.ozon.ru/v1/product/info/stocks-by-warehouse/fbs
     */
    public function findAll(): Generator|false
    {
        /**
         * Формируем массив для отправки JSON
         * Пример запроса:
         *  "filter": {
         *
         * "offer_id": [
         *           "136834"
         *       ],
         *       "product_id": [
         *           "214887921"
         *       ],
         *       "visibility": "ALL"
         *       },
         *  "last_id": "",
         *  "limit": 100
         *
         */
        $filter["offer_id"] = $this->article;
        $filter["visibility"] = "ALL";

        if($this->product)
        {
            $filter["product_id"] = $this->product;
        }

        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                '/v4/product/info/stocks',
                [
                    "json" => [
                        'filter' => $filter,
                        //"last_id" => "",
                        "limit" => 100
                    ]
                ]
            );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            $this->logger->critical($content['code'].': '.$content['message'], [self::class.':'.__LINE__]);
            return false;
        }

        if(!isset($content['items']))
        {
            return false;
        }

        foreach($content['items'] as $item)
        {
            // Фильтрация по FBS идентификатору склада
            $filter = array_filter($item['stocks'], function($stock) {
                return $stock['type'] === 'fbs' && in_array($this->getWarehouse(), $stock['warehouse_ids'], true);
            });

            if(empty($filter))
            {
                continue;
            }

            yield new OzonStockInfoDTO($item);
        }
    }
}
