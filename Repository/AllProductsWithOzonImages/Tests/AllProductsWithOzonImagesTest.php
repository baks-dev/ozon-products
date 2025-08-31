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

namespace BaksDev\Ozon\Products\Repository\AllProductsWithOzonImages\Tests;

use BaksDev\Ozon\Products\Form\OzonFilter\OzonProductsFilterDTO;
use BaksDev\Ozon\Products\Repository\AllProductsWithOzonImages\AllProductsWithOzonImagesInterface;
use BaksDev\Ozon\Products\Repository\AllProductsWithOzonImages\AllProductsWithOzonImagesRepository;
use BaksDev\Ozon\Products\Repository\AllProductsWithOzonImages\AllProductsWithOzonImagesResult;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('ozon-products')]
#[When(env: 'test')]
final class AllProductsWithOzonImagesTest extends KernelTestCase
{
    public function testFindAll(): void
    {
        /** @var AllProductsWithOzonImagesRepository $AllProductsWithOzonImagesRepository */
        $AllProductsWithOzonImagesRepository = $this->getContainer()->get(AllProductsWithOzonImagesInterface::class);

        $ozonFilter = new OzonProductsFilterDTO();
        $result = $AllProductsWithOzonImagesRepository
            ->filterOzonProducts($ozonFilter)
            ->findAll()
            ->getData();

        /** @var AllProductsWithOzonImagesResult $item */
        foreach($result as $item)
        {
            self::assertInstanceOf(ProductUid::class, $item->getId());
            self::assertInstanceOf(ProductEventUid::class, $item->getEvent());
            self::assertInstanceOf(ProductInvariableUid::class, $item->getInvariable());
            self::assertIsString($item->getProductName());
            self::assertTrue(
                $item->getProductOfferId() === null ||
                $item->getProductOfferId() instanceof ProductOfferUid
            );
            self::assertTrue(
                $item->getProductOfferValue() === null ||
                is_string($item->getProductOfferValue())
            );
            self::assertTrue(
                $item->getProductOfferConst() === null ||
                $item->getProductOfferConst() instanceof ProductOfferConst
            );
            self::assertTrue(
                $item->getProductOfferPostfix() === null ||
                is_string($item->getProductOfferPostfix())
            );
            self::assertTrue(
                $item->getProductOfferReference() === null ||
                is_string($item->getProductOfferReference())
            );
            self::assertTrue(
                $item->getProductVariationId() === null ||
                $item->getProductVariationId() instanceof ProductVariationUid
            );
            self::assertTrue(
                $item->getProductVariationValue() === null ||
                is_string($item->getProductVariationValue())
            );
            self::assertTrue(
                $item->getProductVariationConst() === null ||
                $item->getProductVariationConst() instanceof ProductVariationConst
            );
            self::assertTrue(
                $item->getProductVariationPostfix() === null ||
                is_string($item->getProductVariationPostfix())
            );
            self::assertTrue(
                $item->getProductVariationReference() === null ||
                is_string($item->getProductVariationReference())
            );
            self::assertTrue(
                $item->getProductModificationId() === null ||
                $item->getProductModificationId() instanceof ProductModificationUid
            );
            self::assertTrue(
                $item->getProductModificationValue() === null ||
                is_string($item->getProductModificationValue())
            );
            self::assertTrue(
                $item->getProductModificationConst() === null ||
                $item->getProductModificationConst() instanceof ProductModificationConst
            );
            self::assertTrue(
                $item->getProductModificationPostfix() === null ||
                is_string($item->getProductModificationPostfix())
            );
            self::assertTrue(
                $item->getProductModificationReference() === null ||
                is_string($item->getProductModificationReference())
            );

            self::assertTrue(
                $item->getProductImage() === null ||
                is_string($item->getProductImage())
            );

            self::assertTrue(
                $item->getProductImageExt() === null ||
                is_string($item->getProductImageExt())
            );

            self::assertTrue(
                $item->getProductImageCdn() === null ||
                is_bool($item->getProductImageCdn())
            );


            self::assertTrue(
                $item->getOzonProductImage() === null ||
                is_string($item->getOzonProductImage())
            );

            self::assertTrue(
                $item->getOzonProductImageExt() === null ||
                is_string($item->getOzonProductImageExt())
            );

            self::assertTrue(
                $item->getOzonProductImageCdn() === null ||
                is_bool($item->getOzonProductImageCdn())
            );

            break;
        }
    }

}