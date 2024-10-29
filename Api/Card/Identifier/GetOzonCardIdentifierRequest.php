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

namespace BaksDev\Ozon\Products\Api\Card\Identifier;

use BaksDev\Ozon\Api\Ozon;
use DateInterval;
use Symfony\Contracts\Cache\ItemInterface;

final class GetOzonCardIdentifierRequest extends Ozon
{

    private string $article;

    public function article(string $article): self
    {
        $this->article = $article;
        return $this;
    }


    /**
     * Узнать идентификатор карточки товара по артикулу
     *
     * @see https://api-seller.ozon.ru/v2/product/info
     */
    public function find(): int|false
    {
        $cache = $this->getCacheInit('ozon-products');

        $identifier = $cache->get($this->article, function(ItemInterface $item): int|false {

            $item->expiresAfter(DateInterval::createFromDateString('1 hour'));

            $response = $this->TokenHttpClient()
                ->request(
                    'POST',
                    '/v2/product/info',
                    [
                        "json" => ['offer_id' => $this->article]
                    ]
                );

            $content = $response->toArray(false);

            if($response->getStatusCode() !== 200)
            {
                $this->logger->critical($content['code'].': '.$content['message'], [self::class.':'.__LINE__]);
                return false;
            }

            if(false === isset($content['result']))
            {
                return false;
            }

            return $content['result']['id'];

        });

        return $identifier;
    }
}
