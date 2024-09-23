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

namespace BaksDev\Ozon\Products\Repository\Card\ProductsNotExistsOzonCard;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\DeliveryTransport\Entity\Package\Stocks\DeliveryPackageStocks;
use BaksDev\Ozon\Products\Entity\Card\Ozon\OzonProductsCardOzon;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Entity\Card\Market\YaMarketProductsCardMarket;
use BaksDev\Yandex\Market\Products\UseCase\Cards\NewEdit\Market\YaMarketProductsCardMarketDTO;
use Generator;

final readonly class ProductsNotExistsOzonCard implements ProductsNotExistsOzonCardInterface
{
    public function __construct(
        private DBALQueryBuilder $DBALQueryBuilder,
    ) {
    }

    /**
     * Метод получает все товары, которых нет в карточки Ya Market
     */
    public function findAll(UserProfileUid|string $profile): Generator
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);


        $dbal->from(Product::class, 'product');

        $dbal
            ->leftJoin(
                'product',
                ProductInfo::TABLE,
                'product_info',
                'product_info.product = product.id'
            );

        $dbal->leftJoin(
            'product',
            ProductOffer::class,
            'product_offer',
            'product_offer.event = product.event'
        );


        $dbal->leftJoin(
            'product',
            ProductVariation::class,
            'product_variation',
            'product_variation.offer = product_offer.id'
        );

        $dbal->leftJoin(
            'product',
            ProductModification::class,
            'product_modification',
            'product_modification.variation = product_variation.id'
        );


        /** Проверяем, чтобы не было на доставке упаковки */
        $dbal->andWhereNotExists(
            OzonProductsCardOzon::class,
            'ozon',
            '
                ozon.profile = :profile 
                AND ozon.product = product.id 
                AND ozon.offer = product_offer.const
                AND ozon.variation = product_variation.const
                AND ozon.modification = product_modification.const
            '
        )
            ->setParameter(
                'profile',
                $profile,
                UserProfileUid::TYPE
            );


        /** Параметры конструктора объекта гидрации */

        $dbal->addSelect('
            CASE
               WHEN product_modification.article IS NOT NULL THEN product_modification.article
               WHEN product_variation.article IS NOT NULL THEN product_variation.article
               WHEN product_offer.article IS NOT NULL THEN product_offer.article
               WHEN product_info.article IS NOT NULL THEN product_info.article
               ELSE NULL
            END AS sku
        ');

        $dbal->addSelect('product.id AS product');
        $dbal->addSelect('product_offer.const AS offer');
        $dbal->addSelect('product_variation.const AS variation');
        $dbal->addSelect('product_modification.const AS modification');


        return $dbal->fetchAllHydrate(OzonProductsCardOzonDTO::class, 'newInstance');
    }
}
