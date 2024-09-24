<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Type\Collection;

use BaksDev\Ozon\Products\Mapper\Type\OzonProductsTypeInterface;

final class TiresPassengerCarsOzonProductsType implements OzonProductsTypeInterface
{
    // 17027495 - "Автотовары"
    // 94765 - "Шины для легковых автомобилей"


    private const int ID        = 94765;

    private const int CATEGORY  = 17027949;

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
