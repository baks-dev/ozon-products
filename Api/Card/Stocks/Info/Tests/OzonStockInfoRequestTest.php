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

namespace BaksDev\Ozon\Products\Api\Card\Stocks\Info\Tests;

use BaksDev\Ozon\Orders\Type\ProfileType\TypeProfileFbsOzon;
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonProductStockDTO;
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonStockInfoDTO;
use BaksDev\Ozon\Products\Api\Card\Stocks\Info\OzonStockInfoRequest;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use BaksDev\Ozon\Type\Authorization\OzonAuthorizationToken;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 * @group ozon-products-api
 */
#[When(env: 'test')]
class OzonStockInfoRequestTest extends KernelTestCase
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

    public function testEnv(): void
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

    public function testComplete(): void
    {
        self::bootKernel();

        // Предполагается, что эти UUID соответствуют данным, загруженным вашими тестовыми фикстурами.
        $productUid = new ProductUid($_SERVER['TEST_OZON_PRODUCT']);
        $offerConst = new ProductOfferConst($_SERVER['TEST_OZON_OFFER_CONST']);
        $variationConst = new ProductVariationConst($_SERVER['TEST_OZON_VARIATION_CONST']);
        $modificationConst = new ProductModificationConst($_SERVER['TEST_OZON_MODIFICATION_CONST']);


        /** @var ProductsOzonCardInterface $ProductsOzonCard */
        $ProductsOzonCardRepository = self::getContainer()->get(ProductsOzonCardInterface::class);

        /** @var ProductsOzonCardResult $ProductsOzonCardResult */
        $ProductsOzonCardResult = $ProductsOzonCardRepository
            ->forProduct($productUid)
            ->forOfferConst($offerConst)
            ->forVariationConst($variationConst)
            ->forModificationConst($modificationConst)
            ->find();



        /** @var OzonStockInfoRequest $OzonStockInfoRequest */
        $OzonStockInfoRequest = self::getContainer()->get(OzonStockInfoRequest::class);
        $OzonStockInfoRequest->TokenHttpClient(self::$Authorization);

        $OzonStockInfo = $OzonStockInfoRequest
            ->article($ProductsOzonCardResult->getArticle())
            ->findAll();

        if(false === $OzonStockInfo || false === $OzonStockInfo->valid())
        {
            echo PHP_EOL.'ozon-products: Не найдено информации о количестве'.PHP_EOL;
            self::assertTrue(true);
            return;
        }

        foreach($OzonStockInfo as $OzonStockInfoDTO)
        {
            // Вызываем все геттеры
            $reflectionClass = new ReflectionClass(OzonStockInfoDTO::class);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach($methods as $method)
            {
                // Методы без аргументов
                if($method->getNumberOfParameters() === 0)
                {
                    // Вызываем метод
                    $method->invoke($OzonStockInfoDTO);
                    self::assertTrue(true);
                }
            }


            if(false === $OzonStockInfoDTO->getStocks()->isEmpty())
            {
                /** @var OzonProductStockDTO $OzonProductStockDTO */
                foreach($OzonStockInfoDTO->getStocks() as $OzonProductStockDTO)
                {

                    // Вызываем все геттеры
                    $reflectionClass = new ReflectionClass(OzonProductStockDTO::class);
                    $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

                    foreach($methods as $method)
                    {
                        // Методы без аргументов
                        if($method->getNumberOfParameters() === 0)
                        {
                            // Вызываем метод
                            $method->invoke($OzonProductStockDTO);
                            self::assertTrue(true);
                        }
                    }
                }
            }
        }
    }

}
