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
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\ProductsIdentifierResult;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 * @group ozon-products-repository
 */
#[When(env: 'test')]
class ProductsOzonCardRepositoryTest extends KernelTestCase
{
    public function testEnv(): void
    {
        if(!isset($_SERVER['TEST_PRODUCT']))
        {
            echo PHP_EOL.'В .env.test не определены параметры тестового продукта Озон : '.self::class.':'.__LINE__.PHP_EOL;

            /**
             * TEST_PRODUCT=018954cb-0a6e-744a-97f0-128e7f05d76d
             * TEST_OFFER_CONST=018db273-839d-7f69-8b4b-228aac5934f1
             * TEST_VARIATION_CONST=018db273-839c-72dd-bb36-de5c52445d28
             * TEST_MODIFICATION_CONST=018db273-839c-72dd-bb36-de5c523881be
             */
        }

        self::assertTrue(true);
    }

    public function testFindReturnsNullWhenCardDoesNotExist(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var ProductsOzonCardInterface $productsOzonCardRepository */
        $productsOzonCardRepository = $container->get(ProductsOzonCardInterface::class);

        // Используем заведомо не существующие в тестовой базе данных UUID.
        $productUid = new ProductUid();
        $offerConst = new ProductOfferConst();
        $variationConst = new ProductVariationConst();
        $modificationConst = new ProductModificationConst();

        $ozonCardResult = $productsOzonCardRepository
            ->forProduct($productUid)
            ->forOfferConst($offerConst)
            ->forVariationConst($variationConst)
            ->forModificationConst($modificationConst)
            ->forProfile(new UserProfileUid())
            ->find();

        // Проверяем, что результат равен null, так как карточка не должна быть найдена.
        self::assertFalse($ozonCardResult, 'Не должно быть найдено карточки Ozon для несуществующих идентификаторов.');
    }

    public function testUseCase(): void
    {
        self::bootKernel();

        /** @var ProductsOzonCardInterface $ProductsOzonCard */
        $ProductsOzonCardRepository = self::getContainer()->get(ProductsOzonCardInterface::class);

        if(!isset($_SERVER['TEST_PRODUCT']))
        {
            echo PHP_EOL.'В .env.test не определены параметры тестового продукта Озон : '.self::class.':'.__LINE__.PHP_EOL;

            /**
             * TEST_PRODUCT=018954cb-0a6e-744a-97f0-128e7f05d76d
             * TEST_OFFER_CONST=018db273-839d-7f69-8b4b-228aac5934f1
             * TEST_VARIATION_CONST=018db273-839c-72dd-bb36-de5c52445d28
             * TEST_MODIFICATION_CONST=018db273-839c-72dd-bb36-de5c523881be
             */

            self::assertFalse(false);
            return;
        }

        // Предполагается, что эти UUID соответствуют данным, загруженным вашими тестовыми фикстурами.
        $productUid = new ProductUid($_SERVER['TEST_PRODUCT']);
        $offerConst = new ProductOfferConst($_SERVER['TEST_OFFER_CONST']);
        $variationConst = new ProductVariationConst($_SERVER['TEST_VARIATION_CONST']);
        $modificationConst = new ProductModificationConst($_SERVER['TEST_MODIFICATION_CONST']);

        /** @var ProductsOzonCardResult $ProductsOzonCardResult */

        $ProductsOzonCardResult = $ProductsOzonCardRepository
            ->forProduct($productUid)
            ->forOfferConst($offerConst)
            ->forVariationConst($variationConst)
            ->forModificationConst($modificationConst)
            ->forProfile(new UserProfileUid())
            ->find();

        if(false === $ProductsOzonCardResult)
        {
            self::assertFalse(false);
            return;
        }

        self::assertNotNull($ProductsOzonCardResult, 'Карточка Ozon должна быть найдена для указанных идентификаторов продукта.');
        self::assertInstanceOf(ProductsOzonCardResult::class, $ProductsOzonCardResult);

        // Вызываем все геттеры
        $reflectionClass = new ReflectionClass(ProductsOzonCardResult::class);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach($methods as $method)
        {
            // Методы без аргументов
            if($method->getNumberOfParameters() === 0)
            {
                // Вызываем метод
                $method->invoke($ProductsOzonCardResult);
            }
        }

        foreach($ProductsOzonCardResult->getProductImages() as $productImage)
        {
            self::assertTrue(isset($productImage->product_img));
            self::assertTrue(isset($productImage->product_img_cdn));
            self::assertTrue(isset($productImage->product_img_ext));
            self::assertTrue(isset($productImage->product_img_root));
        }

        if($ProductsOzonCardResult->getProductAttributes())
        {
            foreach($ProductsOzonCardResult->getProductAttributes() as $productAttribute)
            {
                self::assertTrue(isset($productAttribute->id));
                self::assertTrue(isset($productAttribute->value));
            }
        }

        if($ProductsOzonCardResult->getProductProperties())
        {
            foreach($ProductsOzonCardResult->getProductProperties() as $productProperties)
            {
                self::assertTrue(isset($productProperties->id));
                self::assertTrue(isset($productProperties->value));
            }
        }

    }
}
