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

namespace BaksDev\Ozon\Products\Api\Settings\Attribute\Tests;

use BaksDev\Ozon\Orders\Type\ProfileType\TypeProfileFbsOzon;
use BaksDev\Ozon\Products\Api\Settings\Attribute\OzonAttributeDTO;
use BaksDev\Ozon\Products\Api\Settings\Attribute\OzonAttributeRequest;
use BaksDev\Ozon\Type\Authorization\OzonAuthorizationToken;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use PHPUnit\Framework\Attributes\Group;
use Psr\Cache\InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('ozon-products')]
#[When(env: 'test')]
class OzonAttributeRequestTest extends KernelTestCase
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
        /** @var OzonAttributeRequest $ozonAttributeRequest */
        $ozonAttributeRequest = self::getContainer()->get(OzonAttributeRequest::class);
        $ozonAttributeRequest->TokenHttpClient(self::$Authorization);

        // 17027949 -Шины
        // 94765 -Шины для легковых автомобилей

        $allAttributes = [

            // Шины
            17027949 => [
                94765, //  Шины для легковых автомобилей
            ],


            // Одежда
            200000933 => [
                93244, // - Футболка
                93080, // - Джинсы
                93080, // - Джинсы
                93253, // - Худи
                93216, // - Свитшот
            ],

            // Аксессуары
            41777465 => [
                93040, // - Бейсболка
            ],

            // Столовая посуда
            17028741 => [
                92499, // - Кружка
            ],

        ];


        foreach($allAttributes as $category => $types)
        {
            foreach($types as $type)
            {
                $attributes = $ozonAttributeRequest->findAll($category, $type);

                // dd(iterator_to_array($attribute));

                if(false === $attributes || false === $attributes->valid())
                {
                    continue;
                }

                foreach($attributes as $OzonAttributeDTO)
                {
                    // Вызываем все геттеры
                    $reflectionClass = new ReflectionClass(OzonAttributeDTO::class);
                    $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

                    foreach($methods as $method)
                    {
                        // Методы без аргументов
                        if($method->getNumberOfParameters() === 0)
                        {
                            // Вызываем метод
                            $data = $method->invoke($OzonAttributeDTO);
                            // dump($data);
                        }
                    }

                    self::assertTrue(true);
                }
            }
        }
    }
}
