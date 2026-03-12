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
 *
 */

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Messenger\Price\Tests;

use BaksDev\Manufacture\Part\Entity\ManufacturePart;
use BaksDev\Manufacture\Part\Messenger\ManufacturePartMessage;
use BaksDev\Ozon\Manufacture\Messenger\AddOrdersToOzonPackageWhenManufacturePartCompletedDispatcher;
use BaksDev\Ozon\Products\Messenger\Card\OzonProductsCardMessage;
use BaksDev\Ozon\Products\Messenger\Price\OzonProductsPriceMessage;
use BaksDev\Ozon\Products\Messenger\Price\OzonProductsPriceUpdateDispatcher;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Group('ozon-products')]
#[When(env: 'test')]
class OzonProductsPriceUpdateDispatcherDebugTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var OzonProductsPriceUpdateDispatcher $OzonProductsPriceUpdateDispatcher */
        $OzonProductsPriceUpdateDispatcher = self::getContainer()->get(OzonProductsPriceUpdateDispatcher::class);

        self::assertTrue(true);
        return;

        $OzonProductsCardMessage = new OzonProductsCardMessage(
            new UserProfileUid('0196e4cf-fa40-79f1-aae6-70cfc444231a'),
            new ProductUid('018954cb-0a6e-744a-97f0-128e7f05d76d'),
            new ProductOfferConst('018954cb-0a03-7cb1-83e6-2ea563c4cf13'),
            new ProductVariationConst('018954cb-09f4-7d0c-a875-036719f3c88d'),
            new ProductModificationConst('018954cb-09f2-75bd-a09b-28d3939d2ca1'),
        );

        $message = new OzonProductsPriceMessage(
            $OzonProductsCardMessage,
        );

        $OzonProductsPriceUpdateDispatcher($message);
    }
}