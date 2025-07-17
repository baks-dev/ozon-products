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

namespace BaksDev\Ozon\Products\Api\Settings\Type\Tests;

use BaksDev\Ozon\Products\Api\Settings\Type\OzonTypeDTO;
use BaksDev\Ozon\Products\Api\Settings\Type\OzonTypeRequest;
use BaksDev\Ozon\Type\Authorization\OzonAuthorizationToken;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 * @group ozon-products-api
 */
#[When(env: 'test')]
class OzonTypeRequestTest extends KernelTestCase
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
    public function testComplete(): void
    {
        /** @var OzonTypeRequest $ozonTypeRequest */
        $ozonTypeRequest = self::getContainer()->get(OzonTypeRequest::class);
        $ozonTypeRequest->TokenHttpClient(self::$Authorization);


        // 17027949 - Шины
        // 200000933 - Одежда
        // 17028741 - Столовая посуда
        // 41777465 - Аксессуары

        $types = $ozonTypeRequest->findAll(17027949);

        dd(iterator_to_array($types));

        if($types->valid())
        {
            /** @var OzonTypeDTO $OzonTypeDTO */
            $OzonTypeDTO = $types->current();

            self::assertNotNull($OzonTypeDTO->getId());
            self::assertIsInt($OzonTypeDTO->getId());

            self::assertNotNull($OzonTypeDTO->getName());
            self::assertIsString($OzonTypeDTO->getName());

        }
        else
        {
            self::assertFalse($types->valid());
        }
    }
}
