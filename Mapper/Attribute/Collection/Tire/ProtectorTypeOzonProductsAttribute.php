<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class ProtectorTypeOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 7385
    //-complex: 0
    //-name: "Тип протектора"
    //-description: "Выберите одно значение из выпадающего списка."
    //-type: "String"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 551

    // "Внедорожный", "Вседорожный", "Дорожный", "Слики"

    private const int CATEGORY = 17027949;

    private const int DICTIONARY = 551;

    private const int ID = 7385;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): mixed
    {
        if(empty($data['product_attributes']))
        {
            return false;
        }

        $attribute = array_filter(
            json_decode($data['product_attributes'], true),
            fn ($n) => self::ID === (int)$n['id']
        );

        if(empty($attribute))
        {
            return false;
        }

        $attr['value'] = $attribute[array_key_first($attribute)]['value'];

        if(self::DICTIONARY)
        {
            $attr['dictionary_value_id'] = self::DICTIONARY;
        }

        return [
            'complex_id' => 0,
            'id' => self::ID,
            'values' => [$attr]
        ];
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
