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

namespace BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\Tests;

use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 */
#[When(env: 'test')]
class ProductsOzonCardRepositoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var AllProductsIdentifierInterface $AllProductsConstIdentifier */
        $AllProductsConstIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);

        /** @var ProductsOzonCardInterface $ProductsOzonCard */
        $ProductsOzonCard = self::getContainer()->get(ProductsOzonCardInterface::class);

        foreach($AllProductsConstIdentifier->findAll() as $product)
        {
            $new = $ProductsOzonCard
                ->forProduct($product['product_id'])
                ->forOfferConst($product['offer_const'])
                ->forVariationConst($product['variation_const'])
                ->forModificationConst($product['modification_const'])
                ->find();

            if($new === false)
            {
                self::assertFalse($new);
                break;
            }

            self::assertTrue(array_key_exists("product_offer_value", $new));
            self::assertTrue(array_key_exists("product_offer_postfix", $new));
            self::assertTrue(array_key_exists("product_variation_value", $new));
            self::assertTrue(array_key_exists("product_variation_postfix", $new));
            self::assertTrue(array_key_exists("product_modification_value", $new));
            self::assertTrue(array_key_exists("product_modification_postfix", $new));
            self::assertTrue(array_key_exists("product_name", $new));
            self::assertTrue(array_key_exists("product_preview", $new));
            self::assertTrue(array_key_exists("length", $new));
            self::assertTrue(array_key_exists("width", $new));
            self::assertTrue(array_key_exists("height", $new));
            self::assertTrue(array_key_exists("weight", $new));
            self::assertTrue(array_key_exists("ozon_category", $new));
            self::assertTrue(array_key_exists("ozon_type", $new));
            self::assertTrue(array_key_exists("category_id", $new));
            self::assertTrue(array_key_exists("product_properties", $new));
            self::assertTrue(array_key_exists("product_attributes", $new));
            self::assertTrue(array_key_exists("product_keywords", $new));
            self::assertTrue(array_key_exists("product_images", $new));
            self::assertTrue(array_key_exists("product_price", $new));
            self::assertTrue(array_key_exists("product_old_price", $new));
            self::assertTrue(array_key_exists("product_currency", $new));
            self::assertTrue(array_key_exists("product_quantity", $new));
            self::assertTrue(array_key_exists("product_article", $new));
            self::assertTrue(array_key_exists("article", $new));

            break;
        }

        self::assertTrue(true);
    }
}
