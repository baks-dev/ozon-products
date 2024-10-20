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

namespace BaksDev\Ozon\Products\Mapper\Type\Tests;

use BaksDev\Ozon\Products\Api\Settings\Type\OzonTypeRequest;
use BaksDev\Ozon\Products\Mapper\Category\OzonProductsCategoryCollection;
use BaksDev\Ozon\Products\Mapper\Category\OzonProductsCategoryInterface;
use BaksDev\Ozon\Products\Mapper\Type\OzonProductsTypeCollection;
use BaksDev\Ozon\Products\Mapper\Type\OzonProductsTypeInterface;
use BaksDev\Ozon\Type\Authorization\OzonAuthorizationToken;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use function PHPUnit\Framework\assertNotEmpty;

/**
 * @group ozon-products
 * @group ozon-products-mapper
 */
#[When(env: 'test')]
class OzonProductsMapperTypeTest extends KernelTestCase
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

        /** @var OzonTypeRequest $ozonTypeRequest */
        $ozonTypeRequest = self::getContainer()->get(OzonTypeRequest::class);
        $ozonTypeRequest->TokenHttpClient(self::$Authorization);

        /** @var OzonProductsCategoryCollection $OzonProductCategoryCollection */
        $OzonProductCategoryCollection = self::getContainer()->get(OzonProductsCategoryCollection::class);

        /** @var OzonProductsTypeCollection $OzonProductTypeCollection */
        $OzonProductTypeCollection = self::getContainer()->get(OzonProductsTypeCollection::class);

        $OzonProductTypeArray       = $OzonProductTypeCollection->cases();
        $OzonProductCategoryArray   = $OzonProductCategoryCollection->cases();


        /** @var OzonProductsTypeInterface $caseType */
        foreach($OzonProductTypeArray as $caseType)
        {
            /** @var OzonProductsCategoryInterface $caseCategory */
            foreach ($OzonProductCategoryArray as $caseCategory)
            {
                if(!$caseType->equalsCategory($caseCategory->getId()))
                {
                    continue;
                }

                $typeIds = iterator_to_array($ozonTypeRequest->findAll($caseCategory->getId()));

                assertNotEmpty(array_filter($typeIds, fn ($n) => $n->getId() === $caseType->getId()));
            }

        }

        self::assertTrue(true);
    }
}
