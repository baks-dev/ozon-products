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

namespace BaksDev\Ozon\Products\Api\Card\Price\Info\Tests;

use BaksDev\Ozon\Products\Api\Card\Price\Info\OzonPriceInfoDTO;
use BaksDev\Ozon\Products\Api\Card\Price\Info\OzonPriceInfoRequest;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Ozon\Type\Authorization\OzonAuthorizationToken;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Iterator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 * @group ozon-products-api
 */
#[When(env: 'test')]
class OzonPriceInfoRequestTest extends KernelTestCase
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


    public function testComplete(): void
    {
        /** @var OzonPriceInfoRequest $OzonPriceInfoRequest */
        $OzonPriceInfoRequest = self::getContainer()->get(OzonPriceInfoRequest::class);
        $OzonPriceInfoRequest->TokenHttpClient(self::$Authorization);

        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);

        /** @var ProductsOzonCardInterface $ozonProductsCard */
        $ozonProductsCard = self::getContainer()->get(ProductsOzonCardInterface::class);


        /** @var Iterator $result */
        $result = $AllProductsIdentifier->findAll();

        $product = $result->current();

        $Card = $ozonProductsCard
            ->forProduct($product['product_id'])
            ->forOfferConst($product['offer_const'])
            ->forVariationConst($product['variation_const'])
            ->forModificationConst($product['modification_const'])
            ->find();


        $OzonStockInfo = $OzonPriceInfoRequest
            ->article([$Card['article']])
            ->findAll();

        if ($OzonStockInfo->valid())
        {
            /** @var OzonPriceInfoDTO $OzonPriceInfoDTO */
            $OzonPriceInfoDTO = $OzonStockInfo->current();

            self::assertNotNull($OzonPriceInfoDTO->getOffer());
            self::assertIsString($OzonPriceInfoDTO->getOffer());

            self::assertNotNull($OzonPriceInfoDTO->getProduct());
            self::assertIsInt($OzonPriceInfoDTO->getProduct());

            self::assertNotEmpty($OzonPriceInfoDTO->getPrice());
            self::assertIsString($OzonPriceInfoDTO->getPrice());

            self::assertNotEmpty($OzonPriceInfoDTO->getOldPrice());
            self::assertIsString($OzonPriceInfoDTO->getOldPrice());

            self::assertNotEmpty($OzonPriceInfoDTO->getCurrencyCode());
            self::assertIsString($OzonPriceInfoDTO->getCurrencyCode());
        }

        self::assertTrue(true);
    }

}
