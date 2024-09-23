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

namespace BaksDev\Ozon\Products\Api\Settings\Attribute;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Ozon\Api\Ozon;
use DomainException;
use Generator;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class OzonAttributeRequest extends Ozon
{
    private Locale|false $local = false;

    private const int EXPIRES_CACHE_AFTER = 3600;

    public function local(Locale|string $locale): self
    {
        if(is_string($locale))
        {
            $locale = new Locale($locale);
        }

        $this->local = $locale;

        return $this;
    }


    public function findAll(int $categoryId, int $typeId): Generator
    {
        $cache = $this->getCacheInit('ozon-products');

        $response = $cache->get(
            sprintf('%s-%s', 'ozon-products-attribute', $typeId),
            function (ItemInterface $item) use ($categoryId, $typeId): ResponseInterface {

                $item->expiresAfter(self::EXPIRES_CACHE_AFTER);

                /**
                 * Получение характеристик для указанных категории и типа товара.
                 * https://docs.ozon.ru/api/seller/#operation/DescriptionCategoryAPI_GetAttributes
                 */
                return $this->TokenHttpClient()
                    ->request(
                        'POST',
                        '/v1/description-category/attribute',
                        [
                            "json" => [
                                'description_category_id'       => $categoryId,
                                "language"                      => $this->local ?: 'DEFAULT',
                                "type_id"                       => $typeId
                            ]
                        ]
                    );
            }
        );


        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            $this->logger->critical($content['code'].': '.$content['message'], [__FILE__.':'.__LINE__]);

            throw new DomainException(
                message: 'Ошибка '.self::class,
                code: $response->getStatusCode()
            );
        }

        foreach ($content['result'] as $attributes)
        {
            yield new OzonAttributeDTO($attributes);
        }
    }

}
