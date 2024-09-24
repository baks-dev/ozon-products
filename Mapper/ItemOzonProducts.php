<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper;

use BaksDev\Ozon\Products\Mapper\Property\OzonProductsPropertyInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class ItemOzonProducts
{
    /**
     * Основной класс данных
     *
     * Array
     * example: "items" => [
     *     'attributes' => [],
     *     "barcode" => "112772873170",
     *      "description_category_id" => 17028922,
     *      "new_description_category_id" => 0,
     *      "color_image" => "",
     *      "complex_attributes" => [ ],
     *      "currency_code" => "RUB",
     *      "depth" => 10,
     *      "dimension_unit" => "mm",
     *      "height" => 250,
     *      "images" => [ ],
     *      "images360" => [ ],
     *      "name" => "Комплект защитных плёнок для X3 NFC. Темный хлопок",
     *      "offer_id" => "143210608",
     *      "old_price" => "1100",
     *      "pdf_list" => [ ],
     *      "price" => "1000",
     *      "primary_image" => "",
     *      "vat" => "0.1",
     *      "weight" => 100,
     *      "weight_unit" => "g",
     *      "width" => 150
     *   ]
     *
     */

    public function __construct(
        #[AutowireIterator('baks.ozon.product.property', defaultPriorityMethod: 'priority')] private iterable $property
    ) {}


    /**
     * Возвращает состояние
     */
    public function getData(array $product): array|false
    {

        $request = null;

        /** @var OzonProductsPropertyInterface $item */
        foreach($this->property as $item)
        {

            $value = $item->getData($product);

            if($value === null || $value === false)
            {
                continue;
            }

            $request[$item->getValue()] = $value;
        }

        return is_null($request) ? false : $request;
    }
}
