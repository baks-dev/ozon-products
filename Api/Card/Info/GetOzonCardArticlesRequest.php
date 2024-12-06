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

namespace BaksDev\Ozon\Products\Api\Card\Info;

use BaksDev\Ozon\Api\Ozon;
use Generator;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
final class GetOzonCardArticlesRequest extends Ozon
{
    private ?string $last = null;

    private int $limit = 1000;

    /**
     * Список добавленных артикулов
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductInfoPictures
     */
    public function findAll(): Generator|false
    {
        while(true)
        {
            $response = $this->TokenHttpClient()
                ->request(
                    'POST',
                    '/v2/product/list',
                    [
                        "json" => [
                            'last_id' => $this->last ?: '',
                            'limit' => $this->limit
                        ]
                    ]
                );

            $content = $response->toArray(false);

            if($response->getStatusCode() !== 200)
            {
                $this->logger->critical($content['code'].': '.$content['message'], [self::class.':'.__LINE__]);
                return false;
            }

            foreach($content['result']['items'] as $item)
            {
                yield $item['offer_id'];
            }

            $this->last = $content['result']['last_id'];

            if($content['result']['total'] < $this->limit)
            {
                break;
            }
        }
    }
}
