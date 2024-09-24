<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Category\Collection;

use BaksDev\Ozon\Products\Mapper\Category\OzonProductsCategoryInterface;

final class ClothesOzonProductsCategory implements OzonProductsCategoryInterface
{
    // parent category: 15621031
    // id: 200000933
    // name: "Одежда"

    private const int ID = 200000933;

    private const int PARENT = 15621031;

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
