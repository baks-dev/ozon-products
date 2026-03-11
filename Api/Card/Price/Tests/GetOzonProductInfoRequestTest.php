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


use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Core\Type\Locale\Locales\Ru;
use BaksDev\Orders\Order\UseCase\Admin\Edit\Tests\OrderNewTest;
use BaksDev\Ozon\Orders\Type\ProfileType\TypeProfileFbsOzon;
use BaksDev\Ozon\Products\Api\Card\Price\GetOzonProductInfoRequest;
use BaksDev\Ozon\Type\Authorization\OzonAuthorizationToken;
use BaksDev\Products\Product\Repository\CurrentProductByArticle\CurrentProductByBarcodeResult;
use BaksDev\Products\Product\Repository\ProductByArticle\ProductEventByArticleInterface;
use BaksDev\Products\Stocks\UseCase\Admin\Package\Tests\PackageProductStockTest;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Tests\UserNewUserProfileHandleTest;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;


#[Group('get-ozon-product-info-request-test')]
#[When(env: 'test')]
class GetOzonProductInfoRequestTest extends KernelTestCase
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
            TypeProfileFbsOzon::TYPE,
            $_SERVER['TEST_OZON_CLIENT'],
            $_SERVER['TEST_OZON_WAREHOUSE'],
            '10',
            0,
            false,
            false,
        );
    }

    public function testGetOzonProductInfoRequestRepository(): void
    {

        self::assertTrue(true);

        /** @var GetOzonProductInfoRequest $GetOzonProductInfoRequest */
        $GetOzonProductInfoRequest = self::getContainer()->get(GetOzonProductInfoRequest::class);
        $GetOzonProductInfoRequest->TokenHttpClient(self::$Authorization);

        $result = $GetOzonProductInfoRequest->find()
            //->findAll()
            //->find()
        ;

        if(false === $result || false === $result->valid())
        {
            return;
        }


        /** @var ProductEventByArticleInterface $ProductEventByArticleRepository */
        $ProductEventByArticleRepository = self::getContainer()->get(ProductEventByArticleInterface::class);

        foreach($result as $item)
        {
            $ProductEvent = $ProductEventByArticleRepository
                ->findProductEventByArticle($item['offer_id']);


            foreach($ProductEvent->getOffer() as $ProductOffer)
            {
                foreach($ProductOffer->getVariation() as $ProductVariation)
                {
                    foreach($ProductVariation->getModification() as $ProductModification)
                    {
                        if($ProductModification->getArticle() === $item['offer_id'])
                        {
                            echo $ProductEvent->getNameByLocale(new Locale(Ru::class)).' ';

                            echo $ProductVariation->getValue().'/';
                            echo $ProductModification->getValue().' ';
                            echo $ProductOffer->getValue().';';

                            $price = $ProductModification->getPrice()->getPriceValue();

                            // название / цена сайта / Цена Озон / Минимальная конкурента
                            echo $price->getValue().';'
                                .$item['price']['price']
                                .$item['ozon_index_data']['min_price']
                                .PHP_EOL;
                        }
                    }
                }
            }
        }

        if(false === $result || false === $result->valid())
        {
            return;
        }

        foreach($result as $GetOzonProductInfoRequestResult)
        {
            // Вызываем все геттеры
            $reflectionClass = new ReflectionClass(GetOzonProductInfoRequestResult::class);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach($methods as $method)
            {
                // Методы без аргументов
                if($method->getNumberOfParameters() === 0)
                {
                    // Вызываем метод
                    $data = $method->invoke($GetOzonProductInfoRequestResult);
                    // dump($data);
                }
            }

            break;
        }

    }

}