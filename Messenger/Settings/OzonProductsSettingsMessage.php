<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Messenger\Settings;


use BaksDev\Ozon\Products\Type\Settings\Event\OzonProductsSettingsEventUid;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;

final class OzonProductsSettingsMessage
{
    /**
     * Идентификатор
     */
    private CategoryProductUid $id;

    /**
     * Идентификатор события
     */
    private OzonProductsSettingsEventUid $event;

    /**
     * Идентификатор предыдущего события
     */
    private ?OzonProductsSettingsEventUid $last;


    public function __construct(CategoryProductUid $id, OzonProductsSettingsEventUid $event, ?OzonProductsSettingsEventUid $last = null)
    {
        $this->id = $id;
        $this->event = $event;
        $this->last = $last;
    }


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
