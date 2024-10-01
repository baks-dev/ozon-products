<?php

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
        private  OzonAttributeValueSearchRequest|false $attributeValueSearchRequest = false,
    ) {
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
