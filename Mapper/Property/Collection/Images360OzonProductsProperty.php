<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Property\Collection;

use BaksDev\Ozon\Products\Mapper\Property\OzonProductsPropertyInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.ozon.product.property')]
final class Images360OzonProductsProperty implements OzonProductsPropertyInterface
{
    /**
     * Массив изображений 360. До 70 штук.
     * Формат: адрес ссылки на изображение в общедоступном облачном хранилище.
     * Формат изображения по ссылке — JPG.
     *
     * Array
     * example: "images360": [ ]
     */

    public const PARAM = 'images360';

    public function getValue(): string
    {
        return self::PARAM;
    }

    /**
     * Возвращает состояние
     */
    public function getData(array $data): array
    {
        return [];
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
