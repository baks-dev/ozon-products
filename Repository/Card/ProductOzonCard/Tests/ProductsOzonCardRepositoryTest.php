<?php

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
                continue;
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
