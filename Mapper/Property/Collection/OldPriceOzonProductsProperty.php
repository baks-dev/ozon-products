<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Property\Collection;

use BaksDev\Ozon\Products\Mapper\Property\OzonProductsPropertyInterface;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.ozon.product.property')]
final class OldPriceOzonProductsProperty implements OzonProductsPropertyInterface
{
    /**
     * Цена до скидок (будет зачёркнута на карточке товара). Указывается в рублях.
     * Разделитель дробной части — точка, до двух знаков после точки.
     * Если вы раньше передавали old_price, то при обновлении price также обновите old_price.
     *
     * string
     * example: "old_price": "1100"
     */

    public const PARAM = 'old_price';

    public function getValue(): string
    {
        return self::PARAM;
    }

    /**
     * Возвращает состояние
     */
    public function getData(array $data): string|false
    {
        if(empty(['product_old_price']))
        {
            return false;
        }

        $oldPrice =  new Money($data['product_old_price'], true);

        return (string)$oldPrice->getValue();
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
