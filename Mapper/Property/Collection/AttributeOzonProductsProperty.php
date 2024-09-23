<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Property\Collection;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Property\OzonProductsPropertyInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.ozon.product.property')]
final class AttributeOzonProductsProperty implements OzonProductsPropertyInterface
{
    /**
     * Аттрибуты.
     *
     * Array
     * example: "attributes": [
     *     'complex_id' => 0,
     *     'id' => 85,
     *     'values' => []
     *   ]
     *
     */

    public const PARAM = 'attributes';

    public function __construct(
        private ?ItemOzonProductsAttribute $attributes = null,
    ) {
    }


    public function getValue(): string
    {
        return self::PARAM;
    }

    /**
     * Возвращает состояние
     */
    public function getData(array $data): mixed
    {
        if($this->attributes !== null && !empty($data))
        {
            return $this->attributes->getData($data);
        }

        return false;
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
