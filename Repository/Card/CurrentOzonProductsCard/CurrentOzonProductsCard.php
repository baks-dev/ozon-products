<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Repository\Card\CurrentOzonProductsCard;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Ozon\Products\Entity\Card\Ozon\OzonProductsCardOzon;
use BaksDev\Ozon\Products\Entity\Card\OzonProductsCard;
use BaksDev\Type\Card\Id\OzonProductsCardUid;

final readonly class CurrentOzonProductsCard implements CurrentOzonProductsCardInterface
{
    public function __construct(private DBALQueryBuilder $DBALQueryBuilder)
    {
    }

    public function findByCard(OzonProductsCardUid|string $card): array|bool
    {
        if(is_string($card))
        {
            $card = new OzonProductsCardUid($card);
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->from(OzonProductsCard::class, 'card')
            ->where('card.id = :card')
            ->setParameter('card', $card, OzonProductsCardUid::TYPE);

        $dbal
            ->addSelect('ozon_card.profile')
            ->addSelect('ozon_card.sku AS article')
            ->addSelect('ozon_card.product AS product_uid')
            ->addSelect('ozon_card.offer AS offer_const')
            ->addSelect('ozon_card.variation AS variation_const')
            ->addSelect('ozon_card.modification AS modification_const')
            ->leftJoin(
                'card',
                OzonProductsCardOzon::class,
                'ozon_card',
                'ozon_card.main = card.id'
            );


        return $dbal
            ->enableCache('products-product', 86400)
            ->fetchAssociative();
    }
}
