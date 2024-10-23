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

namespace BaksDev\Ozon\Products\Mapper\Category\Tests;

use BaksDev\Ozon\Products\Api\Settings\Category\OzonCategoryRequest;
use BaksDev\Ozon\Products\Mapper\Category\OzonProductsCategoryCollection;
use BaksDev\Ozon\Products\Mapper\Category\OzonProductsCategoryInterface;
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
class OzonProductsMapperCategoryTest extends KernelTestCase
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

        /** @var OzonProductsCategoryCollection $OzonProductCategoryCollection */
        $OzonProductCategoryCollection = self::getContainer()->get(OzonProductsCategoryCollection::class);

        $OzonProductCategoryArray = $OzonProductCategoryCollection->cases();

        /** @var OzonProductsCategoryInterface $case */
        foreach($OzonProductCategoryArray as $case)
        {
            self::assertNotEmpty(array_filter(
                iterator_to_array($ozonCategoryRequest->findAll($case->getParentId())),
                fn($n) => $n->getId() === $case->getId()
            ));

        }
        self::assertTrue(true);
    }
}
