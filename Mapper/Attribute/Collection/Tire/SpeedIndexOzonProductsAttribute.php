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

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchRequest;
use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SpeedIndexOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 7390
    //-complex: 0
    //-name: "Индекс скорости"
    //-description: "Параметр, характеризующий максимальную скорость, на которую рассчитана шина. Выберите одно значение из выпадающего списка."
    //-type: "String"
    //-collection: false
    //-required: true
    //-count: 0
    //-groupId: 108
    //-groupName: "Технические свойства"
    //-dictionary: 760

    /** 17027949 - Шины */
    public const int CATEGORY = 17027949;

    public const int TYPE = 94765;

    public const int ID = 7390;

    private false|OzonAttributeValueSearchRequest $attributeValueRequest;


    public function getId(): int
    {
        return self::ID;
    }

    public function getData(ProductsOzonCardResult $data, ?TranslatorInterface $translator): array|false
    {
        if(empty($data->getProductModificationPostfix()))
        {
            return false;
        }

        $cleaned_str = preg_replace('/[^a-zA-Z]/u', '', $data->getProductModificationPostfix());

        /** Преобразуем русские символы в латиницу */
        $cleaned_str = str_replace(
            ['Н', 'К', 'М', 'Р', 'Т'], // русские символы
            ['H', 'K', 'M', 'P', 'T'], // латиница
            $cleaned_str
        );

        $speedIndex = null;

        if(!empty($cleaned_str))
        {
            $speedIndex = self::getConvertValue($cleaned_str);
        }

        $requestData = new ItemDataBuilderOzonProductsAttribute(
            self::ID,
            $speedIndex,
            $data,
            $this->attributeValueRequest
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

    public static function getConvertValue(?string $value): ?string
    {
        if(null === $value)
        {
            return null;
        }

        return match ($value)
        {
            'H' => 'H',
            'J' => 'J',
            'K' => 'K',
            'L' => 'L',
            'M' => 'M',
            'N' => 'N',
            'P' => 'P',
            'Q' => 'Q',
            'R' => 'R',
            'S' => 'S',
            'T' => 'T',
            'V' => 'V',
            'W' => 'W',
            'Y' => 'Y',
            'Z/ZR' => 'Z/ZR',
            default => null,
        };
    }

    public function attributeValueRequest(
        OzonAttributeValueSearchRequest|false $attributeValueRequest,
        ?TranslatorInterface $translator = null
    ): void
    {
        $this->attributeValueRequest = $attributeValueRequest;
    }
}
