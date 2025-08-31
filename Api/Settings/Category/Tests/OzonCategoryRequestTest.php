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

namespace BaksDev\Ozon\Products\Api\Settings\Category\Tests;

use BaksDev\Ozon\Orders\Type\ProfileType\TypeProfileFbsOzon;
use BaksDev\Ozon\Products\Api\Settings\Category\OzonCategoryDTO;
use BaksDev\Ozon\Products\Api\Settings\Category\OzonCategoryRequest;
use BaksDev\Ozon\Type\Authorization\OzonAuthorizationToken;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use PHPUnit\Framework\Attributes\Group;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('ozon-products')]
#[When(env: 'test')]
class OzonCategoryRequestTest extends KernelTestCase
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

    /**
     * @throws InvalidArgumentException
     */
    public function testComplete(): void
    {
        /** @var OzonCategoryRequest $ozonCategoryRequest */
        $ozonCategoryRequest = self::getContainer()->get(OzonCategoryRequest::class);
        $ozonCategoryRequest->TokenHttpClient(self::$Authorization);

        // Автотовары 17027495
        // Одежда 15621031
        // Дом и сад 17027494

        $categories = $ozonCategoryRequest->findAll(17027495);


        // dd(iterator_to_array($categories));
        // выбираем категорию и находим тип
        // @see OzonTypeRequestTest

        if($categories->valid())
        {
            /** @var OzonCategoryDTO $OzonCategoryDTO */
            $OzonCategoryDTO = $categories->current();

            self::assertNotNull($OzonCategoryDTO->getId());
            self::assertIsInt($OzonCategoryDTO->getId());

            self::assertNotNull($OzonCategoryDTO->getName());
            self::assertIsString($OzonCategoryDTO->getName());

        }
        else
        {
            self::assertFalse($categories->valid());
        }
    }
}
