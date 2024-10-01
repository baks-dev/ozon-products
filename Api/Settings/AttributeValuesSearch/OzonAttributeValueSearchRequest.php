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

namespace BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch;

use BaksDev\Ozon\Api\Ozon;
use DateInterval;
use DomainException;
use Generator;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Возвращает справочные значения характеристики по заданному значению value в запросе.
 * @see https://docs.ozon.ru/api/seller/#operation/DescriptionCategoryAPI_SearchAttributeValues
 */
final class OzonAttributeValueSearchRequest extends Ozon
{
    private int $attribute;
    private int $category;
    private int $type;
    private string $value;


    public function attribute(int $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function category(int $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function type(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function value(string $value): self
    {
        $this->value = $value;

        return $this;
    }


    public function find(): Generator
    {
        $cache = $this->getCacheInit('ozon-products');

        $response = $cache->get(
            sprintf('%s-%s-%s', 'ozon-products-attribute-value-search', $this->type, $this->attribute),
            function (ItemInterface $item): ResponseInterface {

                $item->expiresAfter(DateInterval::createFromDateString('1 day'));

                return $this->TokenHttpClient()
                    ->request(
                        'POST',
                        '/v1/description-category/attribute/values/search',
                        [
                            "json" => [
                                "attribute_id"              => $this->attribute,
                                'description_category_id'   => $this->category,
                                "limit"                     => 1,
                                "type_id"                   => $this->type,

                                /**
                                 * Минимальное количество символов в значении 'value' 2
                                 * поэтому добавляем пробел пробел после значения
                                 */
                                "value"  => $this->value . ' '
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

        foreach($content['result'] as $attributeValuesSearch)
        {
            if($this->value !== $attributeValuesSearch['value'])
            {
                $this->logger->critical(
                    sprintf(
                        'Искомое значение %s не совпадает с найденным %s, аттрибут ID = %s',
                        $this->value,
                        $attributeValuesSearch['value'],
                        $this->attribute
                    )
                );
            }

            yield new OzonAttributeValueSearchDTO($attributeValuesSearch);
        }
    }
}