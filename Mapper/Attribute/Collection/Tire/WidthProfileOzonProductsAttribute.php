<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class WidthProfileOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 7387
    //-complex: 0
    //-name: "Ширина профиля, мм"
    //-description: "Расстояние между наружными сторонами боковин шины в накачанном состоянии, измеряется в миллиметрах. Выберите одно значение из выпадающего списка."
    //-type: "String"
    //-collection: false
    //-required: true
    //-count: 0
    //-groupId: 108
    //-groupName: "Технические свойства"
    //-dictionary: 1230


    private const int CATEGORY = 17027949;

    private const int DICTIONARY = 1230;

    private const int ID = 7387;

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
        return true;
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
