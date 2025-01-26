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

namespace BaksDev\Ozon\Products\Api\Card\Identifier;

use BaksDev\Ozon\Api\Ozon;
use DateInterval;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Cache\ItemInterface;

#[Autoconfigure(public: true)]
final class GetOzonCardNameRequest extends Ozon
{
    /**
     * Идентификатор товара в системе Ozon — SKU.
     */
    private int|false $sku = false;

    public function sku(string|int $sku): self
    {
        $this->sku = (int) $sku;
        return $this;
    }


    /**
     * Метод возвращает название товара с артикулом по SKU
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_GetProductInfoList
     */
    public function find(): string|false
    {
        $cache = $this->getCacheInit('ozon-products');

        $key = md5($this->getProfile().$this->sku);
        $cache->delete($key);

        $data = $cache->get($key, function(ItemInterface $item): array|false {

            $item->expiresAfter(DateInterval::createFromDateString('1 hour'));

            $response = $this->TokenHttpClient()
                ->request(
                    'POST',
                    '/v3/product/info/list',
                    [
                        "json" => ['sku' => [$this->sku]]
                    ]
                );

            $content = $response->toArray(false);

            if($response->getStatusCode() !== 200)
            {
                $this->logger->critical(
                    sprintf('ozon-products: шибка при получении идентификатора карточки товара по артикулу %s', $this->article),
                    [$content, self::class.':'.__LINE__]);

                return false;
            }

            if(true === empty($content['items']))
            {
                return false;
            }

            if(true === (count($content['items']) !== 1))
            {
                $this->logger->critical(
                    sprintf('ozon-products: Не найдено, либо найдено более одного идентификатора карточки товара %s', $this->article),
                    [$content['items'], self::class.':'.__LINE__]
                );

                return false;
            }

            return current($content['items']);

        });

        return empty($data) ? false : $data['name'].' ('.$data['offer_id'].')';
    }

}
