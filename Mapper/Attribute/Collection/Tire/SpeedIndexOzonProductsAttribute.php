<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

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

    private const int CATEGORY = 17027949;

    private const int DICTIONARY = 760;

    private const int ID = 7390;


    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): mixed
    {
        if(empty($data['product_modification_postfix']))
        {
            return false;
        }

        $cleaned_str = preg_replace('/[^a-zA-Z]/u', '', $data['product_modification_postfix']);

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

        $requestData = new ItemDataOzonProductsAttribute(
            self::ID,
            $speedIndex,
            self::DICTIONARY
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
        if(null === $value) return null;

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
}
