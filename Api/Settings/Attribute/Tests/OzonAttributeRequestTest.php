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

namespace BaksDev\Ozon\Products\Api\Settings\Attribute\Tests;

use BaksDev\Ozon\Products\Api\Settings\Attribute\OzonAttributeDTO;
use BaksDev\Ozon\Products\Api\Settings\Attribute\OzonAttributeRequest;
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
class OzonAttributeRequestTest extends KernelTestCase
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

    /**
     * @throws InvalidArgumentException
     */
    public function testComplete(): void
    {
        /** @var OzonAttributeRequest $ozonAttributeRequest */
        $ozonAttributeRequest = self::getContainer()->get(OzonAttributeRequest::class);
        $ozonAttributeRequest->TokenHttpClient(self::$Authorization);

        // 17027949 -Шины
        // 94765 -Шины для легковых автомобилей

        // 200000933 - Одежда
        // 93244 - Футболка
        // 93080 - Джинсы
        // 93080 - Джинсы
        // 93253 - Худи
        // 93216 - Свитшот

        // 41777465 - Аксессуары
        // 93040 - Бейсболка

        // 17028741 - Столовая посуда
        // 92499 - Кружка

        $attribute = $ozonAttributeRequest->findAll(17027949, 94765);

//                dd(iterator_to_array($attribute));

        if($attribute->valid())
        {
            /** @var OzonAttributeDTO $OzonAttributeDTO */
            $OzonAttributeDTO = $attribute->current();

            self::assertNotNull($OzonAttributeDTO->getId());
            self::assertIsInt($OzonAttributeDTO->getId());

            self::assertNotNull($OzonAttributeDTO->getName());
            self::assertIsString($OzonAttributeDTO->getName());

            self::assertNotNull($OzonAttributeDTO->getComplexId());
            self::assertIsInt($OzonAttributeDTO->getComplexId());

            self::assertNotNull($OzonAttributeDTO->getDescription());
            self::assertIsString($OzonAttributeDTO->getDescription());

            self::assertNotNull($OzonAttributeDTO->getType());
            self::assertIsString($OzonAttributeDTO->getType());

            self::assertNotNull($OzonAttributeDTO->isCollection());
            self::assertIsBool($OzonAttributeDTO->isCollection());

            self::assertNotNull($OzonAttributeDTO->isRequired());
            self::assertIsBool($OzonAttributeDTO->isCollection());

            self::assertNotNull($OzonAttributeDTO->getMaxValueCount());
            self::assertIsInt($OzonAttributeDTO->getMaxValueCount());

            self::assertNotNull($OzonAttributeDTO->getGroupName());
            self::assertIsString($OzonAttributeDTO->getGroupName());

            self::assertNotNull($OzonAttributeDTO->getGroupId());
            self::assertIsInt($OzonAttributeDTO->getGroupId());

            self::assertNotNull($OzonAttributeDTO->getDictionaryId());
            self::assertIsInt($OzonAttributeDTO->getDictionaryId());

        }
        else
        {
            self::assertFalse($attribute->valid());
        }

    }
}
