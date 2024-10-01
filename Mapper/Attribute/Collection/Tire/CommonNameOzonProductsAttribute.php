<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class CommonNameOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 9048
    //-complex: 0
    //-name: "Название модели (для объединения в одну карточку)"
    //-description: "Укажите название модели товара. Не указывайте в этом поле тип и бренд."
    //-type: "String"
    //-collection: false
    //-required: true
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 0

    /** 17027949 - Шины */
    private const int CATEGORY = 17027949;

    private const int ID = 9048;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): array|false
    {
        if(empty($data['product_article']))
        {
            return false;
        }

        $requestData = new ItemDataBuilderOzonProductsAttribute(
            self::ID,
            $data['product_article'],
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
        return true;
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
