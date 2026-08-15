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

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection;

use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use BaksDev\Ozon\Products\Mapper\Category\Collection\AccessoriesOzonProductsCategory;
use BaksDev\Ozon\Products\Mapper\Category\Collection\ClothesOzonProductsCategory;
use BaksDev\Ozon\Products\Mapper\Category\Collection\CookwareOzonProductsCategory;
use BaksDev\Ozon\Products\Mapper\Type\Collection\BaseBallsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\HoodieOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\JeansOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\LongsleeveOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\MugsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\SweatshirtsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\TShirtsOzonProductsType;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use Symfony\Contracts\Translation\TranslatorInterface;

final class Sign18PlusOzonProductsAttribute implements OzonProductsAttributeInterface
{
    /**
     * -id: 9070
     * -complex: 0
     * -name: "Признак 18+"
     * -description: "Признак для товаров, которые содержат эротику, сцены секса, изображения с нецензурными
     * выражениями, даже если они написаны частично или со спец. символами, а также для товаров категории 18+ (только
     * для взрослых)."
     * -type: "Boolean"
     * -collection: false
     * -required: false
     * -count: 0
     * -groupId: 0
     * -groupName: ""
     * -dictionary: 0
     * */

    public const array CATEGORY = [
        AccessoriesOzonProductsCategory::ID, // 41777465 - "Аксессуары"
        ClothesOzonProductsCategory::ID, // 200000933 - "Одежда"
        CookwareOzonProductsCategory::ID, // 17028741 - "Столовая посуда"
        // TireOzonProductsCategory::ID, // 17027949 - "Шины"
    ];

    public const int ID = 9070;

    public const array TYPES = [
        BaseBallsOzonProductsType::ID, // 93040 - Бейсболка
        HoodieOzonProductsType::ID, // 93253 - "Худи"
        JeansOzonProductsType::ID, // 93080 - "Джинсы"
        LongsleeveOzonProductsType::ID, // 93148 - "Лонгслив"
        MugsOzonProductsType::ID, // 92499 - Кружка
        SweatshirtsOzonProductsType::ID, //  93216 - "Свитшот"
        // TiresPassengerCarsOzonProductsType::ID, // 94765 - "Шины для легковых автомобилей"
        TShirtsOzonProductsType::ID, // 93244 - "Футболка"
    ];

    private const int DICTIONARY = 0;

    public static function priority(): int
    {
        return 100;
    }

    public static function equals(int|string $param): bool
    {
        return self::ID === (int) $param;
    }

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(ProductsOzonCardResult $data, ?TranslatorInterface $translator): array|false
    {
        return false;
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
        //return ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight'];
        return false;
    }

    public function equalsCategory(int $category): bool
    {
        return in_array($category, self::CATEGORY, true);
    }

    public function equalsType(int $type): bool
    {
        return in_array($type, self::TYPES, true);
    }
}
