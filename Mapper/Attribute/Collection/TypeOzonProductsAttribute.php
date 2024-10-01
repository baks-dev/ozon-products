<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection;

use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchRequest;
use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

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

    private const int ID = 8229;

    private false|OzonAttributeValueSearchRequest $attributeValueRequest;

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
            self::getConvertValue(current($attribute)->value),
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
        return true;
    }

    public static function getConvertValue(?string $value): ?string
    {
        return match ($value)
        {
            'jeep'          => 'Шины для внедорожника',
            'bus', 'truck'  => 'Шины для грузовых автомобилей',
            'passenger'     => 'Шины для легковых автомобилей',
            default         => null,
        };
    }

    public function attributeValueRequest(OzonAttributeValueSearchRequest|false $attributeValueRequest): void
    {
        $this->attributeValueRequest = $attributeValueRequest;
    }
}
