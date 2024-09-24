<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Category\Collection;

use BaksDev\Ozon\Products\Mapper\Category\OzonProductsCategoryInterface;

final class DishesOzonProductsCategory implements OzonProductsCategoryInterface
{
    // parent category: 17027494
    // id: 17028741
    // name: "Столовая посуда"

    private const int ID = 17028741;

    private const int PARENT = 17027494;

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
