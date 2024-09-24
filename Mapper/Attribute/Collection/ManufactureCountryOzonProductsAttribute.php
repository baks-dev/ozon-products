<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ManufactureCountryOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 4389
    //-complex: 0
    //-name: "Страна-изготовитель"
    //-description: "Выберите одно или несколько значений из списка. В xls-файле варианты заполняются через точку с запятой (;) без пробелов."
    //-type: "String"
    //-collection: true
    //-required: false
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 1935



    //    private const int CATEGORY = 17027949;

    private const int DICTIONARY = 1935;

    private const int ID = 4389;

    public function __construct(
        private ?TranslatorInterface $translator = null,
    ) {
    }

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): mixed
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

        $country = '';

        if($this->translator)
        {
            $country = $this->translator->trans(
                $attribute[array_key_first($attribute)]->value,
                domain: 'field-country'
            );
        }

        $requestData = new ItemDataOzonProductsAttribute(
            self::ID,
            $country,
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
        return true;
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
