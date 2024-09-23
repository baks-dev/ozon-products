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

namespace BaksDev\Ozon\Products\Mapper\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Ozon\Products\Mapper\ItemOzonProducts;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Yandex\Market\Products\Mapper\YandexMarketMapper;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsCardMessage;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use BaksDev\Yandex\Market\Products\Repository\Card\ProductYaMarketCard\ProductsYaMarketCardInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @group ozon-products
 */
#[When(env: 'test')]
class OzonMapperTest extends KernelTestCase
{
    public function testUseCase(): void
    {

        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);

        /** @var ProductsOzonCardInterface $ProductsOzonCard */
        $ProductsOzonCard = self::getContainer()->get(ProductsOzonCardInterface::class);

        /** @var ItemOzonProducts $ItemOzonProducts */
        $itemOzonProducts = self::getContainer()->get(ItemOzonProducts::class);



        foreach($AllProductsIdentifier->findAll() as $item)
        {
            $OzonCard = $ProductsOzonCard
                ->forProduct($item['product_id'])
                ->forOfferConst($item['offer_const'])
                ->forVariationConst($item['variation_const'])
                ->forModificationConst($item['modification_const'])
                ->find();

            if($OzonCard === false)
            {
                continue;
            }


            $request = $itemOzonProducts->getData($OzonCard);


            self::assertEquals($request['description_category_id'], $OzonCard['ozon_category']);

            self::assertIsString($request["color_image"]);
            self::assertNotEmpty($request["attributes"]);

            self::assertNotEmpty($request["dimension_unit"]);
            self::assertIsString($request["dimension_unit"]);

            self::assertIsArray($request["images360"]);
            self::assertIsArray($request["complex_attributes"]);

            self::assertNotNull($request['currency_code']);
            self::assertIsString($request['currency_code']);

            self::assertEquals($request['offer_id'], $OzonCard['article']);
            self::assertNotFalse(stripos($request['name'], $OzonCard['product_name']));

            self::assertEquals($request['price'], $OzonCard['product_price'] / 100);
            self::assertEquals($request['width'], $OzonCard['width'] / 10);
            self::assertEquals($request['height'], $OzonCard['height'] / 10);
            self::assertEquals($request['depth'], $OzonCard['height'] / 10);
            self::assertEquals($request['weight'], $OzonCard['weight'] / 100);

            self::assertIsArray($request["images"]);
            self::assertIsArray($request["pdf_list"]);

            self::assertNotNull($request['vat']);
            self::assertNotNull($request['weight_unit']);

            break;
        }

        self::assertTrue(true);


    }
}
