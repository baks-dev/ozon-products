<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Type\Collection;

use BaksDev\Ozon\Products\Mapper\Type\OzonProductsTypeInterface;

final class HoodieOzonProductsType implements OzonProductsTypeInterface
{
    // 200000933 - "Одежда"
    // 93253 - "Худи"

    private const int ID        = 93253;

    private const int CATEGORY  = 200000933;

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
