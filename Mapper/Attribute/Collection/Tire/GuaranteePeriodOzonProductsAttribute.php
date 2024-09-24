<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use BaksDev\Ozon\Products\Mapper\Category\Collection\TireOzonProductsCategory;
use BaksDev\Ozon\Products\Type\Settings\Property\OzonProductProperty;

final class GuaranteePeriodOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 4385
    //-complex: 0
    //-name: "Гарантийный срок"
    //-description: "Укажите гарантийный срок. Гарантийный срок – это период, в течение которого изготовитель гарантирует качество товара и обязуется принять данный товар у потребителя для проведения проверки качества (экспертизы) и выполнения предусмотренных законом требований."
    //-type: "String"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 1
    //-groupName: "Общие"
    //-dictionary: 0



    private const int CATEGORY = 17027949;

    private const int DICTIONARY = 0;

    private const int ID = 4385;

    private const string GUARANTEE_PERIOD = '5 лет';

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): mixed
    {
        $requestData = new ItemDataOzonProductsAttribute(
            self::ID,
            self::GUARANTEE_PERIOD,
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
        return self::CATEGORY === $category;
    }
}
