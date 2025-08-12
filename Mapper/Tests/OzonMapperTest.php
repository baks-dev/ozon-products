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

namespace BaksDev\Ozon\Products\Mapper\Tests;

use BaksDev\Ozon\Orders\Type\ProfileType\TypeProfileFbsOzon;
use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchRequest;
use BaksDev\Ozon\Products\Mapper\OzonProductsMapper;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use BaksDev\Ozon\Type\Authorization\OzonAuthorizationToken;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
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
            new UserProfileUid('018d464d-c67a-7285-8192-7235b0510924'),
            $_SERVER['TEST_OZON_TOKEN'],
            TypeProfileFbsOzon::TYPE,
            $_SERVER['TEST_OZON_CLIENT'],
            $_SERVER['TEST_OZON_WAREHOUSE'],
            '10',
            0,
            false,
            false,
        );
    }

    public function testEnv()
    {
        if(!isset($_SERVER['TEST_OZON_PRODUCT']))
        {
            echo PHP_EOL.'В .env.test не определены параметры тестового продукта Озон : '.self::class.':'.__LINE__.PHP_EOL;

            /**
             * TEST_OZON_PRODUCT=018954cb-0a6e-744a-97f0-128e7f05d76d
             * TEST_OZON_OFFER_CONST=018db273-839d-7f69-8b4b-228aac5934f1
             * TEST_OZON_VARIATION_CONST=018db273-839c-72dd-bb36-de5c52445d28
             * TEST_OZON_MODIFICATION_CONST=018db273-839c-72dd-bb36-de5c523881be
             */
        }

        self::assertTrue(true);
    }


    public function testUseCase(): void
    {
        if(!isset($_SERVER['TEST_OZON_PRODUCT']))
        {
            self::assertTrue(true);
            return;
        }

        self::bootKernel();

        /** @var OzonAttributeValueSearchRequest $OzonAttributeValueSearchRequest */
        $OzonAttributeValueSearchRequest = self::getContainer()->get(OzonAttributeValueSearchRequest::class);
        $OzonAttributeValueSearchRequest->TokenHttpClient(self::$Authorization);


        $productUid = new ProductUid($_SERVER['TEST_OZON_PRODUCT']);
        $offerConst = new ProductOfferConst($_SERVER['TEST_OZON_OFFER_CONST']);
        $variationConst = new ProductVariationConst($_SERVER['TEST_OZON_VARIATION_CONST']);
        $modificationConst = new ProductModificationConst($_SERVER['TEST_OZON_MODIFICATION_CONST']);

        /** @var ProductsOzonCardInterface $ProductsOzonCard */
        $ProductsOzonCardRepository = self::getContainer()->get(ProductsOzonCardInterface::class);

        /** @var ProductsOzonCardResult $ProductsOzonCardResult */
        $ProductsOzonCardResult = $ProductsOzonCardRepository
            ->forProfile(new UserProfileUid())
            ->forProduct($productUid)
            ->forOfferConst($offerConst)
            ->forVariationConst($variationConst)
            ->forModificationConst($modificationConst)
            ->find();


        if(false === $ProductsOzonCardResult)
        {
            self::assertFalse(false);
            return;
        }


        /** @var OzonProductsMapper $OzonProductsMapper */
        $OzonProductsMapper = self::getContainer()->get(OzonProductsMapper::class);
        $Card = $OzonProductsMapper->getData($ProductsOzonCardResult);
        self::assertTrue(true);

        // dd($Card);
    }
}
