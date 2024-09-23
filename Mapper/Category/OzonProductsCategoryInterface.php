<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Category;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.ozon.product.category')]
interface OzonProductsCategoryInterface
{
    /** Возвращает ID категории */
    public function getId(): int;

    /** Возвращает ID верхнего уровня категории */
    public function getParentId(): int;

    /** Добавить в настройку */
    public function isSetting(): bool;
}
