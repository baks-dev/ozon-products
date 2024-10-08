<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class AnnotationOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 4191
    //-complex: 0
    //-name: "Аннотация"
    //-description: "Описание товара, маркетинговый текст. Необходимо заполнять на русском языке."
    //-type: "multiline"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 0

    private const int ID = 4191;

    public function getId(): int
    {
        return self::ID;
    }


    public function getData(array $data): array|false
    {

        if(empty($data['product_preview']))
        {
            return false;
        }

        $requestData = new ItemDataBuilderOzonProductsAttribute(
            self::ID,
            $data['product_preview']
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
