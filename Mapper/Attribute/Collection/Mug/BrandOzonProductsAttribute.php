<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Mug;

use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;

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
    public const int CATEGORY = 17028741;

    private const int DICTIONARY = 28732849;

    public const int ID = 85;

    public function getId(): int
    {
        return self::ID;
    }

    /** Возвращает состояние */
    public function getData(ProductsOzonCardResult $data): array|false
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
