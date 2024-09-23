<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class TireFeatureOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 7394
    //-complex: 0
    //-name: "Особенности шин"
    //-description: "Выберите одно или несколько значений из списка. В xls-файле варианты заполняются через точку с запятой (;) без пробелов."
    //-type: "String"
    //-collection: true
    //-required: false
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 1091

    // "12 линий шипов",  "Воздушные амортизаторы шипа", "Двухслойная фиксация шипов", "Индикатор износа",
    // "Инфо-участок", "Инфо-участок", "Резиновая смесь FLEX - ICE",  "Система Eco Stud", "Система Eco Stud",
    // "15 линий шипов", "Трехмерные ламели Stabiligrip", "Шестигранный якорный шип", "Некондиция"

    private const int CATEGORY = 17027949;

    private const int DICTIONARY = 1091;

    private const int ID = 7394;

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
