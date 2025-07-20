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
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;

final class UpdateOzonProductPriceRequest extends Ozon
{
    private string $article;

    private Money $price;

    private Money $oldPrice;

    private Currency|false $currency = false;

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

    public function oldPrice(Money $price): self
    {
        $this->oldPrice = $price;
        return $this;
    }

    public function currency(Currency $currency): self
    {
        $this->currency = $currency;
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

        /**
         * Обновляем цены только если указан флаг обновления карточки
         */

        if($this->isCard() === false)
        {
            return true;
        }


        $prices["offer_id"] = $this->article;

        /**
         * @note ВАЖНО! Торговая надбавка присваивается при расчете стоимости услуг
         */

        // Цена в промежутке от 400 до 10000 — по правилам скидка должна быть больше 5%

        // Старая цена
        $oldPrice = clone $this->price;
        $oldPrice->applyString('6%');
        $prices['old_price'] = (string) $oldPrice->getRoundValue();

        // текущая стоимость
        $prices["price"] = (string) $this->price->getRoundValue();

        // Минимальная цена
        $minPrice = clone $this->price;
        $minPrice->applyString('-6%');
        $prices["min_price"] = (string) $minPrice->getRoundValue();

        // Валюта
        $prices['currency_code'] = $this->currency instanceof Currency ? $this->currency->getCurrencyValueUpper() : 'RUB';

        // Атрибут для включения и выключения автоматического применения к товару доступных акций Ozon:
        $prices['auto_action_enabled'] = 'DISABLED';

        // Атрибут для включения и выключения авто добавления товара в акции:
        $prices['auto_add_to_ozon_actions_list_enabled'] = 'DISABLED';

        // Атрибут для авто применения стратегий цены:
        $prices['price_strategy_enabled'] = 'DISABLED';

        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                '/v1/product/import/prices',
                [
                    "json" => [
                        'prices' => [$prices],
                    ],
                ],
            );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            $this->logger->critical(
                sprintf('ozon-products: Ошибка при обновление стоимости артикула %s', $this->article),
                [$content, $prices, self::class.':'.__LINE__],
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
