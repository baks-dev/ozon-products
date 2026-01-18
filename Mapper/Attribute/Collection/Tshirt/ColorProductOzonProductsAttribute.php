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

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tshirt;

use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchRequest;
use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ColorProductOzonProductsAttribute implements OzonProductsAttributeInterface
{
    /**
     * -id: 10096
     * -complex: 0
     * -name: "Цвет товара"
     * -description: "Основной или доминирующий цвет товара. Если точного соответствия нет, используйте ближайшие
     * цвета. Сложные цвета нужно описывать перечислением простых цветов. Например, если в товаре преобладают черный,
     * желтый и белый цвета, то укажите их все простым перечислением. Атрибут "Цвет товара" — это основной цвет. Все
     * остальные цвета можно перечислить в атрибуте "Название цвета", если он доступен в категории"
     * -type: "String"
     * -collection: true
     * -required: true
     * -count: 0
     * -groupId: 0
     * -groupName: ""
     * -dictionary: 1494
     * */

    /** 200000933 -Одежда */
    public const int CATEGORY = 200000933;

    public const int ID = 10096;

    private const int DICTIONARY = 1494;

    private false|OzonAttributeValueSearchRequest $attributeValueRequest;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(ProductsOzonCardResult $data, ?TranslatorInterface $translator): array|false
    {
        if(empty($data->getProductAttributes()))
        {
            return false;
        }

        $attribute = array_filter(
            $data->getProductAttributes(),
            static fn($n) => self::ID === (int) $n->id,
        );

        $value = empty($attribute) ? $this->default() : current($attribute)->value;

        if($translator instanceof TranslatorInterface)
        {
            $value = $translator->trans($value, domain: 'color_type');
        }

        $requestData = new ItemDataBuilderOzonProductsAttribute(
            self::ID,
            $value,
            $data,
            $this->attributeValueRequest,
        );

        return $requestData->getData();
    }

    public function attributeValueRequest(OzonAttributeValueSearchRequest|false $attributeValueRequest): void
    {
        $this->attributeValueRequest = $attributeValueRequest;
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
