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

namespace BaksDev\Ozon\Products\Api\Card\Price\Tests;

use BaksDev\Orders\Order\UseCase\Admin\Edit\Tests\OrderNewTest;
use BaksDev\Ozon\Products\Api\Card\Price\GetOzonProductCalculatorRequest;
use BaksDev\Ozon\Type\Authorization\OzonAuthorizationToken;
use BaksDev\Products\Stocks\UseCase\Admin\Package\Tests\PackageProductStockTest;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Tests\UserNewUserProfileHandleTest;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 */
#[When(env: 'test')]
class GetOzonProductCalculatorRequestTest extends KernelTestCase
{
    private static OzonAuthorizationToken $Authorization;

    public static function setUpBeforeClass(): void
    {
        OrderNewTest::setUpBeforeClass();
        PackageProductStockTest::setUpBeforeClass();
        UserNewUserProfileHandleTest::setUpBeforeClass();

        self::$Authorization = new OzonAuthorizationToken(
            new UserProfileUid('018d464d-c67a-7285-8192-7235b0510924'),
            $_SERVER['TEST_OZON_TOKEN'],
            $_SERVER['TEST_OZON_CLIENT'],
            $_SERVER['TEST_OZON_WAREHOUSE'],
        );
    }

    public function testUseCase(): void
    {
        self::assertTrue(true);

        $GetOzonProductCalculatorRequest = self::getContainer()->get(GetOzonProductCalculatorRequest::class);
        $GetOzonProductCalculatorRequest->TokenHttpClient(self::$Authorization);

        //  - Шины
        $categories = [
            17027949, // Шины
        ];

        foreach($categories as $category)
        {
            $Money = $GetOzonProductCalculatorRequest
                ->category($category)
                ->length((682 / 10)) // Длина 69 см
                ->width((691 / 10)) // Ширина 70 см
                ->height((253 / 10)) // Высота 26 см
                ->weight((12600 / 1000)) // Вес 12.6
                ->price(new Money(7443)) // Цена 7443
                ->calc();

            // 2703
            if($category === 17027949)
            {
                // стоимость услуг FBS = 2714
                self::assertEquals(($Money->getValue() - 7443), 2863);
                self::assertEquals(10306.0, $Money->getValue());
            }
        }
    }
}
