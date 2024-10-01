<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class EquipmentOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 4384
    //-complex: 0
    //-name: "Комплектация"
    //-description: "Перечислите, что входит в комплект вместе с товаром"
    //-type: "multiline"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 0

    /** 17027949 - Шины */
    private const int CATEGORY = 17027949;

    private const int ID = 4384;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): array|false
    {
        if(empty($data['product_attributes']))
        {
            return false;
        }

        $attribute = array_filter(
            json_decode(
                $data['product_attributes'],
                false,
                512,
                JSON_THROW_ON_ERROR
            ),
            fn ($n) => self::ID === (int)$n->id
        );

        if(empty($attribute))
        {
            return false;
        }

        $requestData = new ItemDataBuilderOzonProductsAttribute(
            self::ID,
            current($attribute)->value
        );

        return $requestData->getData();
    }

    public function default(): string|false
    {
        return false;
    }

    public function isSetting(): bool
    {
        return true;
    }

    public function required(): bool
    {
        return false;
    }

    public function choices(): array|false
    {
        return false;
    }

    public static function priority(): int
    {
        return 100;
    }

    public static function equals(int|string $param): bool
    {
        return self::ID === (int) $param;
    }

    public function equalsCategory(int $category): bool
    {
        return self::CATEGORY === $category;
    }
}
