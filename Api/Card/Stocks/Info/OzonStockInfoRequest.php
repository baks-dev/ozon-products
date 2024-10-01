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

namespace BaksDev\Ozon\Products\Api\Card\Stocks\Info;

use App\Kernel;
use BaksDev\Ozon\Api\Ozon;
use DomainException;
use Generator;

/**
 *  Информация о количестве товаров
 * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_GetProductInfoStocksV3
 */
final class OzonStockInfoRequest extends Ozon
{
    private array $article;

    private array|false $product = false;

    public function article(array $article): self
    {
        $this->article = $article;
        return $this;
    }

    public function product(?array $product): self
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

    public function findAll(): Generator
    {
        /**
         * Выполнять операции запроса ТОЛЬКО в PROD окружении
         */
        if($this->isExecuteEnvironment() === false)
        {
            return true;
        }


        $filter["offer_id"] = $this->article;

        $filter["visibility"] = "ALL";

        if ($this->product)
        {
            $filter["product_id"] = $this->product;
        }


        /**
         *
         * Пример запроса:
         * "filter": {
         *      "offer_id": [
         *          "136834"
         *      ],
         *      "product_id": [
         *          "214887921"
         *      ],
         *      "visibility": "ALL"
         *      },
         * "last_id": "",
         * "limit": 100
         *
         */
        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                '/v3/product/info/stocks',
                [
                    "json" => [
                        'filter' => [$filter],
                        "last_id" => "",
                        "limit" => 100
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

        foreach ($content['result']['items'] as $item)
        {
            yield new OzonStockInfoDTO($item);
        }

    }
}
