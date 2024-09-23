<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class PartNumberOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 7236
    //-complex: 0
    //-name: "Партномер (артикул производителя)"
    //-description: "Уникальный код (артикул*) однозначно идентифицирующий деталь автомобиля. *Маркировка завода-изготовителя автомобиля для OE (оригинальных) запчастей или номер детали по каталогу фирмы-производителя для OEM (не оригинальных). Если такого артикула у вас нет- продублируйте сюда артикул товара"
    //-type: "String"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 0



    private const int CATEGORY = 17027949;

    private const int DICTIONARY = 0;

    private const int ID = 7236;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): mixed
    {
        $requestData = new ItemDataOzonProductsAttribute(
            self::ID,
            $data,
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
        return true;
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
