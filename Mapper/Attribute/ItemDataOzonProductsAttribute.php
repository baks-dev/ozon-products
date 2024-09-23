<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute;

final class ItemDataOzonProductsAttribute
{
    public function __construct(
        private readonly int $id,
        private readonly string|array|null $value,
        private readonly ?int $dictionary = 0,
        private readonly ?int $complexId = 0,
    ) {
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

        /* Если value является массивом, обрабатываем его */
        if(is_array($this->value))
        {
            if(empty($this->value['product_attributes']))
            {
                return false;
            }


            /* Ищем значение по Id аттрибута */
            $attribute = array_filter(
                json_decode(
                    $this->value['product_attributes'],
                    false,
                    512,
                    JSON_THROW_ON_ERROR
                ),
                fn ($n) => $this->id === (int)$n->id
            );

            if(empty($attribute))
            {
                return false;
            }

            /* Ищем значение по Id аттрибута */
            $attr['value'] = $attribute[array_key_first($attribute)]->value;
        }
        else
        {
            /* Если value является строкой, передаем в результат */
            $attr['value'] = $this->value;
        }


        if ($this->dictionary)
        {
            $attr['dictionary_value_id'] = $this->dictionary;
        }

        /* Собираем данные требуемой структуры */
        return [
            'complex_id' => $this->complexId,
            'id' => $this->id,
            'values' => [$attr]
        ];
    }
}
