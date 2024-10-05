<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection;

use BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire\SeasonOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class NameOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 4180
    //-complex: 0
    //-name: "Название"
    //-description: """
    //      Название пишется по принципу:\n
    //      Тип + Бренд + Модель (серия + пояснение) + Артикул производителя + , (запятая) + Атрибут\n
    //      Название не пишется большими буквами (не используем caps lock).\n
    //      Перед атрибутом ставится запятая. Если атрибутов несколько, они так же разделяются запятыми.\n
    //      Если какой-то составной части названия нет - пропускаем её.\n
    //      Атрибутом может быть: цвет, вес, объём, количество штук в упаковке и т.д.\n
    //      Цвет пишется с маленькой буквы, в мужском роде, единственном числе.\n
    //      Слово цвет в названии не пишем.\n
    //      Точка в конце не ставится.\n
    //      Никаких знаков препинания, кроме запятой, не используем.\n
    //      Кавычки используем только для названий на русском языке.\n
    //      Примеры корректных названий:\n
    //      Смартфон Apple iPhone XS MT572RU/A, space black \n
    //      Кеды Dr. Martens Киноклассика, бело-черные, размер 43\n
    //      Стиральный порошок Ariel Магия белого с мерной ложкой, 15 кг\n
    //      Соус Heinz Xtreme Tabasco суперострый, 10 мл\n
    //      Игрушка для животных Четыре лапы "Бегающая мышка" БММ, белый
    //      """
    //-type: "String"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 0

    private const int ID = 4180;

    public function __construct(
        private ?TranslatorInterface $translator = null,
    ) {}

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(array $data): array|false
    {
        if(!isset($data['ozon_category']))
        {
            return false;
        }

        if(isset($data['product_attributes']))
        {
            $productAttributes = json_decode(
                $data['product_attributes'],
                false,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        $name = '';

        if($this->translator)
        {
            $typeName = $this->translator->trans(
                $data['ozon_type'].'.name',
                domain: 'ozon-products.mapper'
            );

            $name .= $typeName.' ';
        }

        $name = mb_strtolower($name);
        $name = mb_ucfirst($name);

        $name .= $data['product_name'];

        if($data['product_variation_value'])
        {
            $name .= ' '.$data['product_variation_value'];
        }

        if($data['product_modification_value'])
        {
            $name .= '/'.$data['product_modification_value'];
        }

        if($data['product_offer_value'])
        {
            $name .= ' R'.$data['product_offer_value'];
        }

        if($data['product_offer_postfix'])
        {
            $name .= ' '.$data['product_offer_postfix'];
        }

        if($data['product_variation_postfix'])
        {
            $name .= ' '.$data['product_variation_postfix'];
        }

        if($data['product_modification_postfix'])
        {
            $name .= ' '.$data['product_modification_postfix'];
        }

        if(isset($productAttributes))
        {
            /** Добавляем к названию сезонность */
            $Season = new SeasonOzonProductsAttribute();

            foreach($productAttributes as $productAttribute)
            {
                if($Season::equals($productAttribute->id))
                {
                    $value = $Season::getConvertName($productAttribute->value);

                    if(false === empty($value))
                    {
                        $name .= ', '.$value;
                    }
                }
            }
        }

        if(isset($productAttributes))
        {
            /** Добавляем к названию назначение */
            $Type = new TypeOzonProductsAttribute();

            foreach($productAttributes as $productAttribute)
            {
                if($Type::equals($productAttribute->id))
                {
                    $value = $Type::getConvertName($productAttribute->value);

                    if(false === empty($value))
                    {
                        $name .= ', '.$value;
                    }
                }
            }
        }

        $requestData = new ItemDataBuilderOzonProductsAttribute(
            self::ID,
            empty($name) ? null : trim($name),
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
