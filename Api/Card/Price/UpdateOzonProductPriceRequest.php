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

namespace BaksDev\Ozon\Products\Api\Card\Price;

use BaksDev\Ozon\Api\Ozon;
use BaksDev\Ozon\Promotion\BaksDevOzonPromotionBundle;
use BaksDev\Reference\Money\Type\Money;

final class UpdateOzonProductPriceRequest extends Ozon
{
    private string $article;

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
     * Обновить цену
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ImportProductsPrices
     *
     * {
     * "prices": [
     * {
     * "auto_action_enabled": "UNKNOWN",
     * "currency_code": "RUB",
     * "min_price": "800",
     * "offer_id": "",
     * "old_price": "0",
     * "price": "1448",
     * "price_strategy_enabled": "UNKNOWN",
     * "product_id": 1386,
     * "quant_size": 1
     * }
     * ]
     * }
     */
    public function update(): bool
    {
        if($this->isExecuteEnvironment() === false)
        {
            $this->logger->critical('Запрос может быть выполнен только в PROD окружении', [self::class.':'.__LINE__]);
            return true;
        }

        $prices['auto_action_enabled'] = 'UNKNOWN';
        $prices["offer_id"] = $this->article;

        /** Присваиваем торговую наценку к стоимости товара */
        if(!empty($this->getPercent()))
        {
            $this->price->applyString($this->getPercent());
            //$percent = $this->price->percent($this->getPercent());
            //$this->price->add($percent);
        }

        $prices["price"] = (string) $this->price->getRoundValue();
        $prices["min_price"] = (string) $this->price->getRoundValue();
        $prices['old_price'] = (string) $this->price->getRoundValue();

        if(class_exists(BaksDevOzonPromotionBundle::class))
        {
            /** Присваиваем стоимость продукта как минимальную цену */
            $prices['min_price'] = (string) $this->price->getRoundValue();

            /** Добавляем 6% для скидки клиенту */
            $this->price->applyPercent(6);
            $prices["price"] = (string) $this->price->getRoundValue();

            /** Завышаем старую цену для бейджика */
            $this->price->applyPercent(6);
            $prices['old_price'] = $this->price->getRoundValue();
        }

        $prices['currency_code'] = 'RUB';
        $prices['price_strategy_enabled'] = 'UNKNOWN';

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
            $this->logger->critical(
                sprintf('ozon-products: Ошибка при обновление стоимости артикула %s', $this->article),
                [$content, self::class.':'.__LINE__]
            );

            return false;
        }

        $result = current($content['result']);


        if($result['updated'] === false)
        {
            foreach($result['errors'] as $error)
            {
                $this->logger->critical($result['offer_id'].': '.$error['message'], [self::class.':'.__LINE__]);

                if(isset($error['code']) && ($error['code'] === 'NOT_FOUND' || $error['code'] === 'TOTAL_CREATE_LIMIT_EXCEEDED'))
                {
                    return true;
                }
            }

            return false;
        }

        return (bool) $result['updated'];
    }
}
