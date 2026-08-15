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

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use BaksDev\Ozon\Products\Mapper\Category\Collection\AccessoriesOzonProductsCategory;
use BaksDev\Ozon\Products\Mapper\Category\Collection\ClothesOzonProductsCategory;
use BaksDev\Ozon\Products\Mapper\Category\Collection\CookwareOzonProductsCategory;
use BaksDev\Ozon\Products\Mapper\Category\Collection\TireOzonProductsCategory;
use BaksDev\Ozon\Products\Mapper\Type\Collection\BaseBallsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\HoodieOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\JeansOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\LongsleeveOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\MugsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\SweatshirtsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\TiresCommercialCarsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\TiresPassengerCarsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\TiresSuvCarsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\TiresTrackCarsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\TShirtsOzonProductsType;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use Symfony\Contracts\Translation\TranslatorInterface;

final class NameOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 4180
    //-complex: 0
    //-name: "Название"
    //-description: """
    //      Название пишется по принципу:\n
    //      Тип + Бренд + Модель (серия + пояснение) + Артикул производителя + , (запятая) + Атрибут\n
    //      Название не пишется большими буквами (не используем caps lock).\n
    //      Перед атрибутом ставится запятая. Если атрибутов несколько, они так же разделяются запятыми.\n
    //      Если какой-то составной части названия нет - пропускаем её.\n
    //      Атрибутом может быть: цвет, вес, объём, количество штук в упаковке и т.д.\n
    //      Цвет пишется с маленькой буквы, в мужском роде, единственном числе.\n
    //      Слово цвет в названии не пишем.\n
    //      Точка в конце не ставится.\n
    //      Никаких знаков препинания, кроме запятой, не используем.\n
    //      Кавычки используем только для названий на русском языке.\n
    //      Примеры корректных названий:\n
    //      Смартфон Apple iPhone XS MT572RU/A, space black \n
    //      Кеды Dr. Martens Киноклассика, бело-черные, размер 43\n
    //      Стиральный порошок Ariel Магия белого с мерной ложкой, 15 кг\n
    //      Соус Heinz Xtreme Tabasco суперострый, 10 мл\n
    //      Игрушка для животных Четыре лапы "Бегающая мышка" БММ, белый
    //      """
    //-type: "String"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 0

    public const array CATEGORY = [
        AccessoriesOzonProductsCategory::ID, // 41777465 - "Аксессуары"
        ClothesOzonProductsCategory::ID, // 200000933 - "Одежда"
        CookwareOzonProductsCategory::ID, // 17028741 - "Столовая посуда"
        TireOzonProductsCategory::ID, // 17027949 - "Шины"
    ];

    private const array TYPES = [
        BaseBallsOzonProductsType::ID, // 93040 - Бейсболка
        HoodieOzonProductsType::ID, // 93253 - "Худи"
        JeansOzonProductsType::ID, // 93080 - "Джинсы"
        LongsleeveOzonProductsType::ID, // 93148 - "Лонгслив"
        MugsOzonProductsType::ID, // 92499 - Кружка
        SweatshirtsOzonProductsType::ID, //  93216 - "Свитшот"
        TShirtsOzonProductsType::ID, // 93244 - "Футболка"

        TiresPassengerCarsOzonProductsType::ID, // 94765 - "Шины для легковых автомобилей"
        TiresSuvCarsOzonProductsType::ID, // 94762 - "Шины для внедорожника",
        TiresTrackCarsOzonProductsType::ID, // 94763 - "Шины для грузовых автомобилей"
        TiresCommercialCarsOzonProductsType::ID, // 97884 - "Шины для коммерческого транспорта"
    ];

    public const int ID = 4180;

    public function __construct(
        private ?TranslatorInterface $translator = null,
    ) {}

    public static function priority(): int
    {
        return 100;
    }

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(ProductsOzonCardResult $data, ?TranslatorInterface $translator): array|false
    {
        if(empty($data->getOzonCategory()))
        {
            return false;
        }

        $name = '';

        if($this->translator)
        {
            $typeName = $this->translator->trans(
                $data->getOzonType().'.name',
                domain: 'ozon-products.mapper',
            );

            $name .= $typeName.' ';
        }

        $name = mb_strtolower($name);
        $name = mb_ucfirst($name);

        $productName = $data->getProductName();

        $name .= trim($productName);

        if($data->getProductVariationValue() && $data->getProductVariationValue() !== 'null')
        {
            $name .= ' '.$data->getProductVariationValue();
        }

        if($data->getProductModificationValue() && $data->getProductModificationValue() !== 'null')
        {
            $name .= '/'.$data->getProductModificationValue();
        }

        if($data->getProductOfferValue() && $data->getProductOfferValue() !== 'null')
        {
            $name .= ' R'.$data->getProductOfferValue();
        }

        if($data->getProductOfferPostfix())
        {
            $name .= ' '.$data->getProductOfferPostfix();
        }

        if($data->getProductVariationPostfix())
        {
            $name .= ' '.$data->getProductVariationPostfix();
        }

        if($data->getProductModificationPostfix())
        {
            $name .= ' '.$data->getProductModificationPostfix();
        }

        if($data->getProductAttributes())
        {
            /** Добавляем к названию сезонность */
            $Season = new SeasonOzonProductsAttribute();

            foreach($data->getProductAttributes() as $productAttribute)
            {
                if($Season::equals($productAttribute->id))
                {
                    $value = $Season::getConvertName($productAttribute->value);

                    if(false === empty($value))
                    {
                        $name .= ', '.$value;
                    }
                }
            }

            /** Добавляем к названию назначение */
            $Type = new TypeOzonProductsAttribute();

            foreach($data->getProductAttributes() as $productAttribute)
            {
                if($Type::equals($productAttribute->id))
                {
                    $value = $Type::getConvertName($productAttribute->value);

                    if(false === empty($value))
                    {
                        $name .= ', '.$value;
                    }
                }
            }
        }

        $requestData = new ItemDataBuilderOzonProductsAttribute(
            self::ID,
            empty($name) ? null : trim($name),
        );

        return $requestData->getData();
    }

    public static function equals(int|string $param): bool
    {
        return self::ID === (int) $param;
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

    public function equalsCategory(int $category): bool
    {
        return in_array($category, self::CATEGORY, true);
    }

    public function equalsType(int $type): bool
    {
        return in_array($type, self::TYPES, true);
    }
}
