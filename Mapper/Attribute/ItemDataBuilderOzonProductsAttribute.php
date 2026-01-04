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

namespace BaksDev\Ozon\Products\Mapper\Attribute;

use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchDTO;
use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchRequest;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;

final class ItemDataBuilderOzonProductsAttribute
{
    private int|false $category = false;

    private int|false $type = false;


    public function __construct(
        private readonly int $id,
        private readonly int|string|null $value,

        private readonly ProductsOzonCardResult|false $data = false,
        private readonly OzonAttributeValueSearchRequest|false $attributeValueSearchRequest = false,
    ) {}


    /**
     * Возвращает состояние
     */
    public function getData(): array|false
    {

        if(empty($this->value))
        {
            return false;
        }

        if(true === ($this->data instanceof ProductsOzonCardResult))
        {
            $attr = [];

            if(
                true === ($this->attributeValueSearchRequest instanceof OzonAttributeValueSearchRequest) &&
                $this->data->getOzonCategory() &&
                $this->data->getOzonType()
            )
            {
                $attrValues = $this->attributeValueSearchRequest
                    ->forTokenIdentifier($this->data->getProfile())
                    ->attribute($this->id)
                    ->category($this->data->getOzonCategory())
                    ->type($this->data->getOzonType())
                    ->value($this->value)
                    ->findAll();

                if($attrValues !== false && $attrValues->valid() !== false)
                {
                    /** @var OzonAttributeValueSearchDTO $OzonAttributeValueSearchDTO */
                    $OzonAttributeValueSearchDTO = $attrValues->current();

                    /** Берем значение 'value' из реквеста */
                    $attr['value'] = $OzonAttributeValueSearchDTO->getValue();
                    $attr['dictionary_value_id'] = $OzonAttributeValueSearchDTO->getId();

                }
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
            'values' => [$attr],
        ];
    }
}
