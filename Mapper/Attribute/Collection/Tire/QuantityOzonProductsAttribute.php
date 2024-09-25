<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class QuantityOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 7202
    //-complex: 0
    //-name: "Количество, штук"
    //-description: "Укажите количество товаров, которые получит покупатель. Пример: если в одной упаковке две детали - укажите 2; если товар один, но поставляется в двух упаковках - укажите 1. Выберите одно значение из выпадающего списка."
    //-type: "String"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 1
    //-groupName: "Общие"
    //-dictionary: 1324


    private const int CATEGORY = 17027949;

    private const int DICTIONARY = 1324;

    private const int ID = 7202;

    private const int QUANTITY_PRODUCT = 1;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): mixed
    {
        $requestData = new ItemDataOzonProductsAttribute(
            self::ID,
            (string)self::QUANTITY_PRODUCT,
            self::DICTIONARY
        );

        return $requestData->getData();
    }

    public function default(): string|false
    {
        return false;
    }

    public function isSetting(): bool
    {
        return false;
    }

    public function required(): bool
    {
        return false;
    }

    public function choices(): array|false
    {
        return false;
    }

    public static function priority(): int
    {
        return 100;
    }

    public static function equals(int|string $param): bool
    {
        return self::ID === (int) $param;
    }

    public function equalsCategory(int $category): bool
    {
        return self::CATEGORY === $category;
    }
}