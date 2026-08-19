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

namespace BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch;

use BaksDev\Ozon\Api\Ozon;
use DateInterval;
use Generator;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Возвращает справочные значения характеристики по заданному значению value в запросе.
 *
 * @see https://docs.ozon.ru/api/seller/#operation/DescriptionCategoryAPI_SearchAttributeValues
 */
#[Autoconfigure(shared: false)]
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
     * ### Русский
     * Стоп-слова, которые игнорируются при поиске по частям.
     *
     * ### English
     * Stop words that are ignored when searching by parts.
     */
    private const array STOP_WORDS = [
        'в', 'на', 'с', 'по', 'как', 'для', 'и', 'а', 'но', 'о', 'у', 'под',
        'над', 'за', 'об', 'выше, ниже', 'сзади',
        'the', 'and', 'for', 'an', 'a', 'of', 'to', 'in', 'on', 'at', 'by', 'from',
    ];

    /**
     * ### Русский
     * Генерирует поисковые запросы на основе разбиения строки на слова.
     *
     * Если передана фраза из нескольких слов, возвращает массив вариантов:
     * 1. Исходный запрос (без изменений)
     * 2. Отдельные слова (кроме стоп-слов)
     * 3. Уменьшенная копия строки (в нижнем регистре)
     *
     * ### English
     * Generates search queries based on splitting the string into words.
     *
     * If a phrase of several words is passed, returns an array of options:
     * 1. Original request (unchanged)
     * 2. Individual words (except stop words)
     * 3. Reduced copy of the line (lowercase)
     *
     * ### Examples
     * ```
     * $this->generateSearchVariants("Шина для легкового");
     * // Возвращает: ["Шина для легкового", "Шина", "легкового"]
     * ```
     */
    private function generateSearchVariants(string $value): array
    {
        $variants = [];

        // 1. Исходный запрос (с пробелом в конце, как и раньше)
        $variants[] = $value;

        // 2. Пытаемся найти по частям (разбиваем на слова)
        $words = preg_split('/\s+/', trim($value));

        if($words && count($words) > 1)
        {
            foreach($words as $word)
            {
                $lowerWord = mb_strtolower(trim($word));

                // Пропускаем стоп-слова и пустые слова
                if(empty($word) || in_array($lowerWord, self::STOP_WORDS, true))
                {
                    continue;
                }

                $variants[] = mb_strlen($word) > 3 ? trim($word) : $word.' ';
            }
        }

        return array_unique($variants);
    }


    /**
     * @return Generator<OzonAttributeValueSearchDTO>|false
     */
    public function findAll(): Generator|false
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
        $key = md5($this->category.$this->type.$this->attribute.$this->value);
        //$cache->deleteItem($key);

        $content = $cache->get($key, function(ItemInterface $item): array|false {

            $item->expiresAfter(DateInterval::createFromDateString('1 second'));


            $search = $this->generateSearchVariants($this->value);


            foreach($search as $value)
            {
                $response = $this->TokenHttpClient()
                    ->request(
                        'POST',
                        '/v1/description-category/attribute/values/search',
                        [
                            "json" => [
                                "attribute_id" => $this->attribute,
                                'description_category_id' => $this->category,
                                "limit" => 10,
                                "type_id" => $this->type,

                                /**
                                 * Минимальное количество символов в значении 'value' 2
                                 * поэтому добавляем пробел пробел после значения
                                 */
                                "value" => 'Свободный',
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


                if(false === $content)
                {
                    continue;
                }

                if(empty($content['result']))
                {
                    continue;
                }

                break;
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
                        'ozon-products: Искомое значение %s не совпадает с найденным %s, аттрибут ID = %s',
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
