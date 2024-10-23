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

namespace BaksDev\Ozon\Products\Mapper\Attribute;

use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchDTO;
use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchRequest;

final class ItemDataBuilderOzonProductsAttribute
{
    private int|false $category = false;

    private int|false $type = false;


    public function __construct(
        private readonly int $id,
        private readonly string|null $value,
        private readonly array $data = [],
        private OzonAttributeValueSearchRequest|false $attributeValueSearchRequest = false,
    )
    {
        if(!empty($this->data))
        {
            /** Получаем категорию */
            if(!empty($data['ozon_category']))
            {
                $this->category = $data['ozon_category'];
            }

            /** Получаем тип */
            if(!empty($data['ozon_type']))
            {
                $this->type = $data['ozon_type'];
            }

        }
    }


    /**
     * Возвращает состояние
     */
    public function getData(): array|false
    {
        if(empty($this->value))
        {
            return false;
        }


        $attr = [];

        if($this->attributeValueSearchRequest && $this->category && $this->type)
        {
            $this->attributeValueSearchRequest->attribute($this->id);
            $this->attributeValueSearchRequest->category($this->category);
            $this->attributeValueSearchRequest->type($this->type);
            $this->attributeValueSearchRequest->value($this->value);

            $attrValues = $this->attributeValueSearchRequest->find();

            if($attrValues->valid())
            {
                /** @var OzonAttributeValueSearchDTO $OzonAttributeValueSearchDTO */
                $OzonAttributeValueSearchDTO = $attrValues->current();

                /** Берем значине 'value' из реквеста */
                $attr['value'] = $OzonAttributeValueSearchDTO->getValue();
                $attr['dictionary_value_id'] = $OzonAttributeValueSearchDTO->getId();

            }

        }

        if(empty($attr['value']))
        {
            /** Если DICTIONARY = 0, присваиваем текущее значение value */
            $attr['value'] = $this->value;
        }

        /** Собираем данные требуемой структуры */
        return [
            'complex_id' => 0,
            'id' => $this->id,
            'values' => [$attr]
        ];
    }
}
