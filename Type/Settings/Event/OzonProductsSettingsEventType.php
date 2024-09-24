<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Type\Settings\Event;

use BaksDev\Core\Type\UidType\UidType;

final class OzonProductsSettingsEventType extends UidType
{
    public function getClassType(): string
    {
        return OzonProductsSettingsEventUid::class;
    }

    public function getName(): string
    {
        return OzonProductsSettingsEventUid::TYPE;
    }
}
