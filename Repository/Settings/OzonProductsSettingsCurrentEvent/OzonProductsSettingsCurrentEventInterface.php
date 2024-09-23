<?php

namespace BaksDev\Ozon\Products\Repository\Settings\OzonProductsSettingsCurrentEvent;

use BaksDev\Ozon\Products\Entity\Settings\Event\OzonProductsSettingsEvent;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

interface OzonProductsSettingsCurrentEventInterface
{
    public function findByProfile(UserProfileUid|string $profile): OzonProductsSettingsEvent|false;
}
