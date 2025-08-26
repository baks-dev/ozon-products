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

namespace BaksDev\Ozon\Products\Api\Card\Update;

use BaksDev\Ozon\Api\Ozon;
use BaksDev\Ozon\Promotion\BaksDevOzonPromotionBundle;
use BaksDev\Reference\Money\Type\Money;

final class UpdateOzonCardRequest extends Ozon
{
    /**
     * Создать или обновить товар
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ImportProductsV3
     */
    public function update(array $card): int|bool
    {
        if($this->isExecuteEnvironment() === false)
        {
            $this->logger->critical('Запрос может быть выполнен только в PROD окружении', [self::class.':'.__LINE__]);
            return true;
        }

        if(false === $this->isCard())
        {
            return true;
        }

        $card['promotions'] = [
            [
                'operation' => 'DISABLE',
                'type' => 'REVIEWS_PROMO',
            ],
            [
                'operation' => 'DISABLE',
                'type' => 'STOCK_DISCOUNT',
            ],
        ];

        $card['vat'] = $this->getVat();

        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                '/v3/product/import',
                [
                    "json" => ['items' => [$card]],
                ],
            );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            $this->logger->critical(
                sprintf('ozon-products: Ошибка %s при обновлении карточки', $content['code']),
                [self::class.':'.__LINE__, $content, $card]);

            return false;
        }

        if(false === isset($content['result']))
        {
            return false;
        }

        return (int) current($content['result']);
    }
}
