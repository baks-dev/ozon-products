<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class SellerCodeOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 9024
    //-complex: 0
    //-name: "Код продавца"
    //-description: "Цифро-буквенный код товара для его учета, является уникальным среди товаров бренда.
    // Не является EAN/серийным номером/штрихкодом, не равен названию модели товара -
    // для этих параметров есть отдельные атрибуты.
    // Артикул выводится в карточке товара на сайте и может использоваться
    // при автоматическом формировании названия товара."
    //-type: "String"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 0

    //    private const int CATEGORY = 17027949;

    private const int DICTIONARY = 0;

    private const int ID = 9024;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): mixed
    {
        if(empty($data['article']))
        {
            return false;
        }

        $requestData = new ItemDataOzonProductsAttribute(
            self::ID,
            $data['article'],
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
        return true;
    }
}
