<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class BrandOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 85
    //-complex: 0
    //-name: "Бренд"
    //-description: "Укажите наименование бренда, под которым произведен товар. Если товар не имеет бренда, используйте значение "Нет бренда"."
    //-type: "String"
    //-collection: false
    //-required: true
    //-count: 0
    //-groupId: 1
    //-groupName: "Общие"
    //-dictionary: 28732849

    /** 17027949 - Шины */
    private const int CATEGORY = 17028922;

    private const int DICTIONARY = 28732849;

    private const int ID = 85;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): mixed
    {

        if(empty($data['product_name']))
        {
            return false;
        }


        /* Берем только name продукта для бренда */

        $name = explode(' ', trim($data['product_name']));

        $requestData = new ItemDataOzonProductsAttribute(
            self::ID,
            $name[0],
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
