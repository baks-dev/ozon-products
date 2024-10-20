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

namespace BaksDev\Ozon\Products\Mapper\Attribute\Tests;

use BaksDev\Ozon\Products\Api\Settings\Attribute\OzonAttributeRequest;
use BaksDev\Ozon\Products\Api\Settings\Category\OzonCategoryRequest;
use BaksDev\Ozon\Products\Api\Settings\Type\OzonTypeRequest;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeCollection;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use BaksDev\Ozon\Products\Mapper\Category\OzonProductsCategoryCollection;
use BaksDev\Ozon\Products\Mapper\Category\OzonProductsCategoryInterface;
use BaksDev\Ozon\Products\Mapper\Type\OzonProductsTypeCollection;
use BaksDev\Ozon\Products\Mapper\Type\OzonProductsTypeInterface;
use BaksDev\Ozon\Type\Authorization\OzonAuthorizationToken;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 * @group ozon-products-mapper
 */
#[When(env: 'test')]
class OzonProductsMapperAttributeTest extends KernelTestCase
{
    private static OzonAuthorizationToken $Authorization;

    public static function setUpBeforeClass(): void
    {
        self::$Authorization = new OzonAuthorizationToken(
            new UserProfileUid(),
            $_SERVER['TEST_OZON_TOKEN'],
            $_SERVER['TEST_OZON_CLIENT'],
            $_SERVER['TEST_OZON_WAREHOUSE'],
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testUseCase(): void
    {
        /** @var OzonCategoryRequest $ozonCategoryRequest */
        $ozonCategoryRequest = self::getContainer()->get(OzonCategoryRequest::class);
        $ozonCategoryRequest->TokenHttpClient(self::$Authorization);

        /** @var OzonAttributeRequest $ozonAttributeRequest */
        $ozonAttributeRequest = self::getContainer()->get(OzonAttributeRequest::class);
        $ozonAttributeRequest->TokenHttpClient(self::$Authorization);

        /** @var OzonTypeRequest $ozonTypeRequest */
        $ozonTypeRequest = self::getContainer()->get(OzonTypeRequest::class);
        $ozonTypeRequest->TokenHttpClient(self::$Authorization);


        /** @var OzonProductsAttributeCollection $OzonProductAttributeCollection */
        $OzonProductAttributeCollection = self::getContainer()->get(OzonProductsAttributeCollection::class);

        /** @var OzonProductsCategoryCollection $OzonProductCategoryCollection */
        $OzonProductCategoryCollection = self::getContainer()->get(OzonProductsCategoryCollection::class);

        /** @var OzonProductsTypeCollection $OzonProductTypeCollection */
        $OzonProductTypeCollection = self::getContainer()->get(OzonProductsTypeCollection::class);


        $OzonProductTypeArray       = $OzonProductTypeCollection->cases();
        $OzonProductCategoryArray   = $OzonProductCategoryCollection->cases();
        $OzonProductAttributeArray  = $OzonProductAttributeCollection->cases();


        /**
         * @var OzonProductsTypeInterface $caseType
         * Коллекция подкатегории продуктов (шины, футболки, ....)
         */
        foreach($OzonProductTypeArray as $caseType)
        {
            /**
             * @var OzonProductsCategoryInterface $caseCategory
             * Коллекция категорий (Одежда, Посуда, ...)
             */
            foreach($OzonProductCategoryArray as $caseCategory)
            {
                if(!$caseType->equalsCategory($caseCategory->getId()))
                {
                    continue;
                }

                //                echo PHP_EOL;
                //                echo 'Категория '.$caseCategory->getId().PHP_EOL;
                //                echo '-- Тип '.$caseType->getId().PHP_EOL;

                /** Получаем все аттрибуты категории и типа  */
                $attributes = iterator_to_array($ozonAttributeRequest->findAll($caseCategory->getId(), $caseType->getId()));


                /**
                 * @var OzonProductsAttributeInterface $caseAttribute
                 * Коллекция аттрибутов категории продукта
                 */
                $isExist = false;

                foreach($OzonProductAttributeArray as $caseAttribute)
                {
                    if(!$caseAttribute->equalsCategory($caseCategory->getId()))
                    {
                        continue;
                    }

                    if(!empty(array_filter($attributes, fn ($n) => $n->getId() === $caseAttribute->getId())))
                    {
                        $isExist = true;
                    }
                }

                self::assertTrue($isExist);
            }

        }

        self::assertTrue(true);
    }
}
