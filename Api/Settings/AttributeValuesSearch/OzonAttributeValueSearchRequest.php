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

namespace BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch;

use BaksDev\Ozon\Api\Ozon;
use DateInterval;
use Generator;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Возвращает справочные значения характеристики по заданному значению value в запросе.
 *
 * @see https://docs.ozon.ru/api/seller/#operation/DescriptionCategoryAPI_SearchAttributeValues
 */
final class OzonAttributeValueSearchRequest extends Ozon
{
    private int $attribute;

    private int|string $value;

    private int|false $category;

    private int|false $type;


    public function attribute(int $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function category(int|false|null $category): self
    {
        if(empty($category))
        {
            $this->category = false;
            return $this;
        }

        $this->category = $category;

        return $this;
    }

    public function type(int|false|null $type): self
    {
        if(empty($type))
        {
            $this->type = false;
            return $this;
        }

        $this->type = $type;

        return $this;
    }

    public function value(string|int $value): self
    {
        $this->value = $value;

        return $this;
    }


    /**
     * @return Generator<int, OzonAttributeValueSearchDTO>|false
     */
    public function find(): Generator|false
    {
        if(empty($this->category))
        {
            return false;
        }

        if(empty($this->type))
        {
            return false;
        }

        $cache = $this->getCacheInit('ozon-products');
        $key = md5($this->type.$this->attribute);

        $content = $cache->get($key, function(ItemInterface $item): array|false {

            $item->expiresAfter(DateInterval::createFromDateString('1 second'));

            $response = $this->TokenHttpClient()
                ->request(
                    'POST',
                    '/v1/description-category/attribute/values/search',
                    [
                        "json" => [
                            "attribute_id" => $this->attribute,
                            'description_category_id' => $this->category,
                            "limit" => 1,
                            "type_id" => $this->type,

                            /**
                             * Минимальное количество символов в значении 'value' 2
                             * поэтому добавляем пробел пробел после значения
                             */
                            "value" => $this->value.' ',
                        ],
                    ],
                );

            $content = $response->toArray(false);

            if($response->getStatusCode() !== 200)
            {
                $this->logger->critical(
                    $content['code'].': '.$content['message'],
                    [
                        "attribute_id" => $this->attribute,
                        'description_category_id' => $this->category,
                        "limit" => 1,
                        "type_id" => $this->type,
                        "value" => $this->value.' ',
                        __FILE__.':'.__LINE__,
                    ],
                );

                return false;
            }

            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            return $content;

        });

        if(false === $content)
        {
            $cache->deleteItem($key);
            return false;
        }

        if(empty($content['result']))
        {
            $cache->deleteItem($key);
            return false;
        }

        foreach($content['result'] as $attributeValuesSearch)
        {
            if(mb_strtolower($this->value) !== mb_strtolower($attributeValuesSearch['value']))
            {
                $this->logger->critical(
                    sprintf(
                        'Искомое значение %s не совпадает с найденным %s, аттрибут ID = %s',
                        $this->value,
                        $attributeValuesSearch['value'],
                        $this->attribute,
                    ), [self::class.':'.__LINE__],
                );
            }

            yield new OzonAttributeValueSearchDTO($attributeValuesSearch);
        }
    }
}
