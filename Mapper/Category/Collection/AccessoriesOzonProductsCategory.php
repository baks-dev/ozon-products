<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Category\Collection;

use BaksDev\Ozon\Products\Mapper\Category\OzonProductsCategoryInterface;

final class AccessoriesOzonProductsCategory implements OzonProductsCategoryInterface
{
    // parent category: 17027493
    // id: 41777465
    // name: "Аксессуары"

    private const int ID = 41777465;

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
