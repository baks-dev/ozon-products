<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Messenger\Settings;

use BaksDev\Ozon\Products\Type\Settings\Event\OzonProductsSettingsEventUid;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;

final class OzonProductsSettingsMessage
{
    public function __construct(
        private readonly CategoryProductUid $id,
        private readonly OzonProductsSettingsEventUid $event,
        private ?OzonProductsSettingsEventUid $last = null
    ) {}


    /**
     * Идентификатор
     */
    public function getId(): CategoryProductUid
    {
        return $this->id;
    }


    /**
     * Идентификатор события
     */
    public function getEvent(): OzonProductsSettingsEventUid
    {
        return $this->event;
    }

    /**
     * Идентификатор предыдущего события
     */
    public function getLast(): ?OzonProductsSettingsEventUid
    {
        return $this->last;
    }

}
