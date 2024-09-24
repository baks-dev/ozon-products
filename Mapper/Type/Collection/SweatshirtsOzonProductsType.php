<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Type\Collection;

use BaksDev\Ozon\Products\Mapper\Type\OzonProductsTypeInterface;

final class SweatshirtsOzonProductsType implements OzonProductsTypeInterface
{
    // 200000933 - "Одежда"
    // 93216 - "Свитшот"

    private const int ID        = 93216;

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
