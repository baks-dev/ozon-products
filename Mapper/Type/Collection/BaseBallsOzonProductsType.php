<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Type\Collection;

use BaksDev\Ozon\Products\Mapper\Type\OzonProductsTypeInterface;

final class BaseBallsOzonProductsType implements OzonProductsTypeInterface
{
    // 41777465 - Аксессуары
    // 93040 - Бейсболка

    private const int ID        = 93040;

    private const int CATEGORY  = 41777465;

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
