<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\Tests;

use BaksDev\Ozon\Orders\Type\ProfileType\TypeProfileFbsOzon;
use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchDTO;
use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchRequest;
use BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire\BrandOzonProductsAttribute;
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
class OzonAttributeValueSearchRequestTest extends KernelTestCase
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
        self::assertTrue(true);

        /** @var OzonAttributeValueSearchRequest $OzonAttributeValueSearch */
        $OzonAttributeValueSearch = self::getContainer()->get(OzonAttributeValueSearchRequest::class);
        $OzonAttributeValueSearch->TokenHttpClient(self::$Authorization);

        // 17027949 - Шины
        // 94765 - Шины для легковых автомобилей
        // 22100 - Омологация

        // 200000933 - Одежда
        // 93244 - Футболка
        // 93080 - Джинсы
        // 93253 - Худи
        // 93216 - Свитшот

        // 41777465 - Аксессуары
        // 93040 - Бейсболка

        // 17028741 - Столовая посуда
        // 92499 - Кружка


        // ["attribute_id" => 23249,
        //"description_category_id" => 17027949,
        //"limit" => 1,
        //"type_id" => 94765,
        //"value" => "1 "


        $BrandOzonProductsAttribute = new BrandOzonProductsAttribute();

        /**
         * @see OzonAttributeRequestTest для типа
         */
        $result = $OzonAttributeValueSearch
            ->attribute($BrandOzonProductsAttribute::ID)
            ->category($BrandOzonProductsAttribute::CATEGORY)
            ->type(94765) // Шины для легковых автомобилей
            ->value("Triangle")
            ->findAll();

        if(false === $result || false === $result->valid())
        {
            echo "Ошибка при получении аттрибутов ".self::class.':'.__LINE__.PHP_EOL;
            return;
        }

        foreach($result as $OzonAttributeValueSearchDTO)
        {
            // Вызываем все геттеры
            $reflectionClass = new ReflectionClass(OzonAttributeValueSearchDTO::class);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach($methods as $method)
            {
                // Методы без аргументов
                if($method->getNumberOfParameters() === 0)
                {
                    // Вызываем метод
                    $data = $method->invoke($OzonAttributeValueSearchDTO);
                    // dump($data);
                }
            }
        }
    }
}
