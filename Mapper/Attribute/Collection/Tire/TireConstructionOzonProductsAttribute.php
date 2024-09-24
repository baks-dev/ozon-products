<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class TireConstructionOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 7383
    //-complex: 0
    //-name: "Конструкция покрышки"
    //-description: "Выберите одно значение из выпадающего списка."
    //-type: "String"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 108
    //-groupName: "Технические свойства"
    //-dictionary: 1143

    // Бескамерная, С камерой.


    private const int CATEGORY = 17027949;

    private const int DICTIONARY = 1143;

    private const int ID = 7383;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): mixed
    {
        $requestData = new ItemDataOzonProductsAttribute(
            self::ID,
            $data,
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
}
