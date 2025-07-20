<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper;

use BaksDev\Ozon\Products\Mapper\Property\OzonProductsPropertyInterface;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class OzonProductsMapper
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
    public function getData(ProductsOzonCardResult $product): array|false
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
