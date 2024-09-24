<?php

namespace BaksDev\Ozon\Products\Entity\Settings\Event;

use BaksDev\Ozon\Products\Type\Settings\Event\OzonProductsSettingsEventUid;

interface OzonProductsSettingsEventInterface
{
    public function getEvent(): ?OzonProductsSettingsEventUid;
}
