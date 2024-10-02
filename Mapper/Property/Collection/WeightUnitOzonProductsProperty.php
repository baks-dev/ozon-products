<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Property\Collection;

use BaksDev\Ozon\Products\Mapper\Property\OzonProductsPropertyInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.ozon.product.property')]
final class WeightUnitOzonProductsProperty implements OzonProductsPropertyInterface
{
    /**
     * Единица измерения веса:
     *
     * g — граммы,
     * kg — килограммы,
     * lb — фунты.
     *
     * string
     * example: "weight_unit": "g"
     */

    public const PARAM = 'weight_unit';

    public function getValue(): string
    {
        return self::PARAM;
    }

    /**
     * Возвращает состояние
     */
    public function getData(array $data): string
    {
        return 'g';
    }

    /**
     * Возвращает значение по умолчанию
     */
    public function default(): string|bool
    {
        return false;
    }

    /**
     * Метод указывает, нужно ли добавить свойство для заполнения в форму
     */
    public function isSetting(): bool
    {
        return false;
    }


    public function required(): bool
    {
        return false;
    }

    public static function priority(): int
    {
        return 100;
    }

    /**
     * Проверяет, относится ли значение к данному объекту
     */
    public static function equals(string $param): bool
    {
        return self::PARAM === $param;
    }

    public function choices(): bool
    {
        return false;
    }

}
