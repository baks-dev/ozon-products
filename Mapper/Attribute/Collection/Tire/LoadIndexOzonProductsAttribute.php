<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchRequest;
use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class LoadIndexOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 7392
    //-complex: 0
    //-name: "Индекс нагрузки"
    //-description: "Параметр обозначающий уровень предельно допустимой нагрузки на одно колесо при движении ТС с максимально допустимой скоростью при заданном давлении в шине. Выберите одно или несколько значений из списка. В xls-файле варианты заполняются через точку с запятой (;) без пробелов."
    //-type: "String"
    //-collection: true
    //-required: true
    //-count: 0
    //-groupId: 108
    //-groupName: "Технические свойства"
    //-dictionary: 561


    /** 17027949 - Шины */
    private const int CATEGORY = 17027949;

    private const int ID = 7392;

    private false|OzonAttributeValueSearchRequest $attributeValueRequest;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): array|false
    {
        if(empty($data['product_modification_postfix']))
        {
            return false;
        }

        $index = explode('/', $data['product_modification_postfix']);
        $cleanedInt = filter_var(current($index), FILTER_SANITIZE_NUMBER_INT);


        $requestData = new ItemDataBuilderOzonProductsAttribute(
            self::ID,
            $cleanedInt,
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

    public function attributeValueRequest(OzonAttributeValueSearchRequest|false $attributeValueRequest): void
    {
        $this->attributeValueRequest = $attributeValueRequest;
    }
}
