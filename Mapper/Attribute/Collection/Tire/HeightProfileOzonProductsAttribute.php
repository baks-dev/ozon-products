<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchRequest;
use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class HeightProfileOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 7388
    //-complex: 0
    //-name: "Высота профиля, %"
    //-description: "Параметр обозначающий  высоту боковины шины, расстояние от диска до наружной кромки колеса. Выберите одно значение из выпадающего списка."
    //-type: "String"
    //-collection: false
    //-required: true
    //-count: 0
    //-groupId: 108
    //-groupName: "Технические свойства"
    //-dictionary: 894

    /** 17027949 - Шины */
    private const int CATEGORY = 17027949;

    private const int ID = 7388;

    private OzonAttributeValueSearchRequest|false $attributeValueRequest;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): array|false
    {
        if(empty($data['product_attributes']))
        {
            return false;
        }

        $attribute = array_filter(
            json_decode(
                $data['product_attributes'],
                false,
                512,
                JSON_THROW_ON_ERROR
            ),
            fn ($n) => self::ID === (int)$n->id
        );

        if(empty($attribute))
        {
            return false;
        }

        $requestData = new ItemDataBuilderOzonProductsAttribute(
            self::ID,
            current($attribute)->value,
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

    public function attributeValueRequest(OzonAttributeValueSearchRequest|false $attributeValueRequest): void
    {
        $this->attributeValueRequest = $attributeValueRequest;
    }
}
