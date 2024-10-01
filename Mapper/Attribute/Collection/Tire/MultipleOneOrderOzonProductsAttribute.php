<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class MultipleOneOrderOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 21497
    //-complex: 0
    //-name: "Кратность покупки"
    //-description: "Если хотите продавать сразу по несколько товаров, укажите минимальное количество для одного заказа. Например, если напишите «4», покупатели смогут заказать 4 или 8 товаров, а 2  — нет. Эта опция работает только на схемах FBS и realFBS. Если ваши товары уже на складе Ozon, продадим с учётом кратности — но создать новую заявку на поставку не получится. Подробнее https://seller-edu.ozon.ru/work-with-goods/trebovaniya-k-kartochkam-tovarov/characteristics/kratnost"
    //-type: "Integer"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 0


    /** 17027949 - Шины */
    private const int CATEGORY = 17027949;

    private const int ID = 21497;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): array|false
    {
        return false;
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
