<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;

final class SeriesOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 12882
    //-complex: 0
    //-name: "Серия"
    //-description: "Укажите название серии/линейки, к которой относится товар. Не указывайте в этом атрибуте иные спецификации."
    //-type: "String"
    //-collection: false
    //-required: true
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 0

    /** 17027949 - Шины */
    private const int CATEGORY = 17027949;

    private const int ID = 12882;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(ProductsOzonCardResult $data): array|false
    {

        if(!empty($data->getProductName()))
        {
            $name = trim($data->getProductName());

            // Найти позицию первого пробела
            $spacePosition = strpos($name, ' ');

            if($spacePosition !== false)
            {
                // Извлечь часть строки после первого пробела
                $name = substr($name, $spacePosition + 1);
            }

            $requestData = new ItemDataBuilderOzonProductsAttribute(
                self::ID,
                $name,
            );

            return $requestData->getData();
        }


        if(!empty($data->getCardArticle()))
        {
            $requestData = new ItemDataBuilderOzonProductsAttribute(
                self::ID,
                $data->getCardArticle(),
            );

            return $requestData->getData();
        }

        if(empty($data->getProductAttributes()))
        {
            return false;
        }

        $attribute = array_filter(
            $data->getProductAttributes(),
            static fn($n) => self::ID === (int) $n->id,
        );

        if(empty($attribute))
        {
            return false;
        }

        $requestData = new ItemDataBuilderOzonProductsAttribute(
            self::ID,
            current($attribute)->value
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
