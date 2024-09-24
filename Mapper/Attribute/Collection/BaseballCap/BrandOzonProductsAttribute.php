<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\BaseballCap;

use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class BrandOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 31
    //-complex: 0
    //-name: "Бренд в одежде и обуви"
    //-description: "Укажите наименование бренда, под которым произведен товар.
    // Если товар не имеет бренда, используйте значение "Нет бренда""
    //-type: "String"
    //-collection: false
    //-required: true
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 28732849

    /** 41777465 - Аксессуары */
    private const int CATEGORY = 41777465;

    private const int DICTIONARY = 28732849;

    private const int ID = 31;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): mixed
    {
        return null;
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
        //return ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight'];
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
