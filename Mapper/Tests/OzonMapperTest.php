<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Products\Mapper\Tests;

use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchRequest;
use BaksDev\Ozon\Products\Mapper\OzonProductsMapper;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Ozon\Type\Authorization\OzonAuthorizationToken;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 */
#[When(env: 'test')]
class OzonMapperTest extends KernelTestCase
{
    private static OzonAuthorizationToken $Authorization;

    public static function setUpBeforeClass(): void
    {
        self::$Authorization = new OzonAuthorizationToken(
            new UserProfileUid(),
            $_SERVER['TEST_OZON_TOKEN'],
            $_SERVER['TEST_OZON_CLIENT'],
        );
    }

    public function testUseCase(): void
    {
        /** @var OzonAttributeValueSearchRequest $ozonAttributeSearchRequest */
        $ozonAttributeSearchRequest = self::getContainer()->get(OzonAttributeValueSearchRequest::class);
        $ozonAttributeSearchRequest->TokenHttpClient(self::$Authorization);

        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);

        /** @var ProductsOzonCardInterface $ProductsOzonCard */
        $ProductsOzonCard = self::getContainer()->get(ProductsOzonCardInterface::class);

        /** @var OzonProductsMapper $itemOzonProducts */
        $itemOzonProducts = self::getContainer()->get(OzonProductsMapper::class);


        foreach($AllProductsIdentifier->findAll() as $item)
        {

            $request = $ProductsOzonCard
                ->forProduct($item['product_id'])
                ->forOfferConst($item['offer_const'])
                ->forVariationConst($item['variation_const'])
                ->forModificationConst($item['modification_const'])
                ->find();



            if($request === false)
            {
                continue;
            }


            $Card = $itemOzonProducts->getData($request);

            //dd($Card);

            self::assertEquals($Card['description_category_id'], $request['ozon_category']);

            self::assertIsString($Card["color_image"]);
            self::assertNotEmpty($Card["attributes"]);

            self::assertNotEmpty($Card["dimension_unit"]);
            self::assertIsString($Card["dimension_unit"]);

            self::assertIsArray($Card["images360"]);
            self::assertIsArray($Card["complex_attributes"]);

            self::assertNotNull($Card['currency_code']);
            self::assertIsString($Card['currency_code']);

            self::assertEquals($Card['offer_id'], $request['article']);

            self::assertEquals($Card['price'], $request['product_price'] / 100);
            self::assertEquals($Card['width'], $request['width']);
            self::assertEquals($Card['height'], $request['height']);
            self::assertEquals($Card['depth'], $request['length']);
            self::assertEquals($Card['weight'], $request['weight'] * 10);

            self::assertIsArray($Card["images"]);
            self::assertIsArray($Card["pdf_list"]);

            self::assertNotNull($Card['vat']);
            self::assertNotNull($Card['weight_unit']);

            break;
        }

        self::assertTrue(true);


    }
}
