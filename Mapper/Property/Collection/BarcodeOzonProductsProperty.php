<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Property\Collection;

use BaksDev\Ozon\Products\Mapper\Property\OzonProductsPropertyInterface;
use BaksDev\Products\Product\Type\Barcode\ProductBarcode;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.ozon.product.property')]
final class BarcodeOzonProductsProperty implements OzonProductsPropertyInterface
{
    /**
     * Штрихкод товара.
     *
     * string
     * example: "barcode": "112772873170"
     */

    public const PARAM = 'barcode';

    public function getValue(): string
    {
        return self::PARAM;
    }

    /**
     * Возвращает состояние
     */
    public function getData(array $data): string|false
    {
        $uuid = $data['id'];

        if(!empty($data['product_offer_const']))
        {
            $uuid = $data['product_offer_const'];
        }

        if(!empty($data['product_variation_const']))
        {
            $uuid = $data['product_variation_const'];
        }

        if(!empty($data['product_modification_const']))
        {
            $uuid = $data['product_modification_const'];
        }

        return ProductBarcode::generate($uuid);
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
        return true;
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
