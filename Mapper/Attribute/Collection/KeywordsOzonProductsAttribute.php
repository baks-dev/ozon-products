<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class KeywordsOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 22336
    //-complex: 0
    //-name: "Ключевые слова"
    //-description: "Через точку с запятой укажите ключевые слова и словосочетания, которые описывают ваш товар. Используйте только соответствующие фактическому товару значения."
    //-type: "String"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 0


    //    private const int CATEGORY = 17027949;

    private const int DICTIONARY = 0;

    private const int ID = 22336;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): mixed
    {
        if(empty($data['product_keywords']))
        {
            return false;
        }

        $requestData = new ItemDataOzonProductsAttribute(
            self::ID,
            $data['product_keywords'],
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
        return true;
    }
}
