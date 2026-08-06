<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Products\Api\Card\Certificate\FindCertByArticle;

use BaksDev\Ozon\Api\Ozon;
use Generator;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(shared: false)]
final class FindOzonCertByArticleRequest extends Ozon
{
    private int $page = 1;

    private int $limit = 100;

    private ?string $article = null;

    public function setArticle(string $article): self
    {
        $this->article = $article;
        return $this;
    }

    /**
     * Список сертификатов
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CertificateList
     */
    public function findAll(): Generator|false
    {
        if(empty($this->article))
        {
            throw new InvalidArgumentException('Invalid Argument Article');
        }

        while(true)
        {
            $response = $this->TokenHttpClient()
                ->request(
                    'POST',
                    '/v1/product/certificate/list',
                    [
                        "json" => [
                            'offer_id' => $this->article,
                            'page' => $this->page,
                            'page_size' => $this->limit,
                        ],
                    ],
                );

            $content = $response->toArray(false);

            if($response->getStatusCode() !== 200)
            {
                $this->logger->critical($content['code'].': '.$content['message'], [self::class.':'.__LINE__]);
                return false;
            }

            $this->page++;

            if(empty($content['result']['certificates']))
            {
                break;
            }

            foreach($content['result']['certificates'] as $item)
            {
                yield new OzonCertificateByArticleResult(...$item);
            }

            if(empty($content['result']['certificates']) || count($content['result']['certificates']) < $this->limit)
            {
                break;
            }
        }
    }
}
