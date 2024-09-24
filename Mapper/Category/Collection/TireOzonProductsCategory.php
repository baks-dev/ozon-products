<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Category\Collection;

use BaksDev\Ozon\Products\Mapper\Category\OzonProductsCategoryInterface;

final class TireOzonProductsCategory implements OzonProductsCategoryInterface
{
    // parent category: 17027495
    // id: 17027949
    // name: "Шины"

    private const int ID = 17027949;

    private const int PARENT = 17027495;

    public function getId(): int
    {
        return self::ID;
    }

    public function getParentId(): int
    {
        return self::PARENT;
    }

    public function isSetting(): bool
    {
        return true;
    }

}
