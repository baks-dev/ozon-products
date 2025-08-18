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

namespace BaksDev\Ozon\Products\Messenger\Stocks\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Ozon\Products\Messenger\Card\OzonProductsCardMessage;
use BaksDev\Ozon\Products\Messenger\Stocks\OzonProductsStocksMessage;
use BaksDev\Ozon\Products\Messenger\Stocks\OzonProductsStocksUpdateDispatcher;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;


/**
 * @group ozon-products
 */
#[Group('ozon-products')]
#[When(env: 'test')]
class OzonProductsStocksDispatcherTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @see OzonProductsStocksMessage */
        $OzonProductsStocksMessage = new OzonProductsStocksMessage(
            new OzonProductsCardMessage
            (
                profile: new UserProfileUid(),
                product: new ProductUid(),
                offerConst: new ProductOfferConst(),
                variationConst: new ProductVariationConst(),
                modificationConst: new ProductModificationConst(),
            ),
        );

        /** @var OzonProductsStocksUpdateDispatcher $OzonProductsStocksUpdateDispatcher */
        $OzonProductsStocksUpdateDispatcher = self::getContainer()->get(OzonProductsStocksUpdateDispatcher::class);

        $OzonProductsStocksUpdateDispatcher($OzonProductsStocksMessage);

        self::assertTrue(true);

    }
}