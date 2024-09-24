<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Type;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.ozon.product.type')]
interface OzonProductsTypeInterface
{
    public function getId(): int;

    /** Добавить в настройку */
    public function isSetting(): bool;

    /** Проверяет, относится ли объект к указанной категории */
    public function equalsCategory(int $category): bool;

}
