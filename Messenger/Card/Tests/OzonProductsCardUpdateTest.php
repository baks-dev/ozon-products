<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Messenger\Card\Tests;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Products\Api\Card\Update\OzonCardUpdateRequest;
use BaksDev\Ozon\Products\Messenger\Card\OzonProductsCardMessage;
use BaksDev\Ozon\Type\Authorization\OzonAuthorizationToken;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Iterator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 *
 * @group ozon-products-messenger
 */
#[When(env: 'test')]
class OzonProductsCardUpdateTest extends KernelTestCase
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

    public function testUseCase(): void
    {

        /** @var MessageDispatchInterface $MessageDispatch */
        $MessageDispatch = self::getContainer()->get(MessageDispatchInterface::class);

        /** @var AllProductsIdentifierInterface $AllProductsConstIdentifier */
        $AllProductsConstIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);

        /** @var Iterator $result */
        $result = $AllProductsConstIdentifier->findAll();

        $product = $result->current();


        /*        array:8 [
        "product_id" => "018b205e-6255-71d2-95d1-da8f59453eb6"
        "product_event" => "018fdf53-ce70-7ea5-95f8-f685fd6e558b"
        "offer_id" => "018fdf53-ce77-7f94-ae7b-70a8a13ebb43"
        "offer_const" => "018b205e-60ca-72b1-bb3e-206020ca6811"
        "variation_id" => "018fdf53-ce77-7f94-ae7b-70a8a16316be"
        "variation_const" => "018b205e-60d0-7940-a3c7-c46a581862fe"
        "modification_id" => "018fdf53-ce78-7c5f-b75b-9102e9908dc5"
        "modification_const" => "018b205e-60d1-73f5-953a-1670c760e5f7"
        ]*/


        $OzonProductsCardMessage = new OzonProductsCardMessage(
            new ProductUid($product['product_id']),
            $product['offer_const'] ? new ProductOfferConst($product['offer_const']) : false,
            $product['variation_const'] ? new ProductVariationConst($product['variation_const']) : false,
            $product['modification_const'] ? new ProductModificationConst($product['modification_const']) : false,
            self::$Authorization->getProfile()
        );


        $MessageDispatch->dispatch($OzonProductsCardMessage);

        self::assertTrue(true);
    }

}
