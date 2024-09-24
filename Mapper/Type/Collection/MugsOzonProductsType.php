<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Type\Collection;

use BaksDev\Ozon\Products\Mapper\Type\OzonProductsTypeInterface;

final class MugsOzonProductsType implements OzonProductsTypeInterface
{
    // 17028741 - Столовая посуда
    // 92499 - Кружка

    private const int ID        = 92499;

    private const int CATEGORY  = 17028741;

    public function getId(): int
    {
        return self::ID;
    }

    public function isSetting(): bool
    {
        return true;
    }

    public function equalsCategory(int $category): bool
    {
        return self::CATEGORY === $category;
    }
}
