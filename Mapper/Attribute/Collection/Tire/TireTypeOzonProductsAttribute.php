<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class TireTypeOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 7381
    //-complex: 0
    //-name: "Вид шин"
    //-description: "Выберите одно значение из выпадающего списка."
    //-type: "String"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 108
    //-groupName: "Технические свойства"
    //-dictionary: 413
    //-example: Шипованные, Нешипованные


    private const int CATEGORY = 17027949;

    private const int DICTIONARY = 413;

    private const int ID = 7381;

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

        $requestData = new ItemDataOzonProductsAttribute(
            self::ID,
            self::getConvertValue($attribute[array_key_first($attribute)]->value),
            self::DICTIONARY
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

    public static function getConvertValue(string|bool|null $value): ?string
    {

        return match ($value)
        {
            'true', true    => 'Шипованные',
            'false', false  => 'Нешипованные',
            default         => null
        };
    }
}
