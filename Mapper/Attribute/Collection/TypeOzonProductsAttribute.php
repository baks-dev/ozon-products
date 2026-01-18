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

use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchRequest;
use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TypeOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 8229
    //-complex: 0
    //-name: "Тип"
    //-description: "Выберите наиболее подходящий тип товара. По типам товары распределяются по категориям на сайте Ozon. Если тип указан неправильно, товар попадет в неверную категорию. Чтобы правильно указать тип, найдите на сайте Ozon товары, похожие на ваш, и посмотрите, какой тип у них указан."
    //-type: "String"
    //-collection: false
    //-required: true
    //-count: 0
    //-groupId: 1
    //-groupName: "Общие"
    //-dictionary: 1960

    public const int ID = 8229;

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

        if(empty($attribute))
        {
            return false;
        }

        $requestData = new ItemDataBuilderOzonProductsAttribute(
            self::ID,
            self::getConvertValue(current($attribute)->value),
            $data,
            $this->attributeValueRequest
        );

        /**
         * Присваиваем результат для type_id
         *
         * @see TypeIdOzonProductsProperty
         */
        $data->setDictionaryValue($requestData->getData());

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
        return true;
    }

    public static function getValue(?string $value): ?string
    {
        return match ($value)
        {
            'jeep' => 'Внедорожники',
            'bus' => 'Коммерческий транспорт',
            'truck' => 'Грузовые автомобили и автобусы',
            'passenger' => 'Легковые автомобили',
            default => null,
        };
    }


    public static function getConvertValue(?string $value): ?string
    {
        return match ($value)
        {
            'jeep' => 'Шины для внедорожника',
            'bus', 'truck' => 'Шины для грузовых автомобилей',
            'passenger' => 'Шины для легковых автомобилей',
            default => null,
        };
    }

    public static function getConvertName(?string $value): ?string
    {
        return match ($value)
        {
            'jeep' => 'для внедорожника',
            'bus', 'truck' => 'для грузовых автомобилей',
            'passenger' => 'для легковых автомобилей',
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
