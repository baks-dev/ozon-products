<?php

namespace BaksDev\Ozon\Products\Repository\Card\CurrentOzonProductsCard;

use BaksDev\Type\Card\Id\OzonProductsCardUid;

interface CurrentOzonProductsCardInterface
{
    public function findByCard(OzonProductsCardUid|string $card): array|bool;
}