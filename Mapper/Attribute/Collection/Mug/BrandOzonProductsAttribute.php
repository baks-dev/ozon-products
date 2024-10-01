<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Mug;

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

    /** 17028741 - Столовая посуда */
    private const int CATEGORY = 17028741;

    private const int DICTIONARY = 28732849;

    private const int ID = 85;

    public function getId(): int
    {
        return self::ID;
    }

    /** Возвращает состояние */
    public function getData(array $data): array|false
    {
        return false;
    }

    /** Возвращает значение по умолчанию */
    public function default(): string|false
    {
        return false;
    }

    /** Указывает, нужно ли добавить свойство для заполнения в форму */
    public function isSetting(): bool
    {
        return true;
    }

    /** Обязательное ли для заполнения */
    public function required(): bool
    {
        return false;
    }

    /** Массив допустимых значений */
    public function choices(): array|false
    {
        //return ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight'];
        return false;
    }

    /** Приоритет */
    public static function priority(): int
    {
        return 100;
    }

    /** Проверяет, относится ли значение к данному объекту */
    public static function equals(int|string $param): bool
    {
        return self::ID === (int) $param;
    }

    /** Проверяет, относится ли объект к указанной категории */
    public function equalsCategory(int $category): bool
    {
        return self::CATEGORY === $category;
    }
}
