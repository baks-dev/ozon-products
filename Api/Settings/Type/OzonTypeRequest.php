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

namespace BaksDev\Ozon\Products\Api\Settings\Type;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Ozon\Api\Ozon;
use DateInterval;
use DomainException;
use Generator;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[Autoconfigure(public: true)]
final class OzonTypeRequest extends Ozon
{
    private Locale|false $local = false;

    private array $types = [];

    public function local(Locale|string $locale): self
    {
        if(is_string($locale))
        {
            $locale = new Locale($locale);
        }

        $this->local = $locale;

        return $this;
    }


    /**
     * Возвращает категории и типы для товаров в виде дерева.
     * @see https://docs.ozon.ru/api/seller/#tag/CategoryAPI
     *
     * @throws InvalidArgumentException
     */
    public function findAll(int $categoryId): Generator
    {
        $cache = $this->getCacheInit('ozon-products');

        $response = $cache->get('ozon-products-type', function(ItemInterface $item): ResponseInterface {

            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            return $this->TokenHttpClient()
                ->request(
                    'POST',
                    '/v1/description-category/tree',
                    [
                        "json" => [
                            "language" => $this->local ?: 'DEFAULT',
                        ]
                    ]
                );
        });

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            $this->logger->critical($content['code'].': '.$content['message'], [__FILE__.':'.__LINE__]);

            throw new DomainException(
                message: 'Ошибка '.self::class,
                code: $response->getStatusCode()
            );
        }

        $this->getTypes($categoryId, $content['result']);

        foreach($this->types as $type)
        {
            if($type['disabled'])
            {
                continue;
            }

            yield new OzonTypeDTO(
                id: $type['type_id'],
                name: $type['type_name']
            );

        }
    }

    /** Обход дерева категорий, получение типов товаров*/
    public function getTypes(int $categoryId, array $category): void
    {
        foreach($category as $item)
        {
            if(!empty($item['children']))
            {
                if($item['description_category_id'] === $categoryId)
                {
                    if($item['disabled'] === true)
                    {
                        continue;
                    }

                    $this->types = $item['children'];
                }

                $this->getTypes($categoryId, $item['children']);
            }
        }

    }
}
