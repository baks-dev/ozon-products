<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Entity\Settings;

use BaksDev\Ozon\Products\Entity\Settings\Event\OzonProductsSettingsEvent;


use BaksDev\Ozon\Products\Type\Settings\Event\OzonProductsSettingsEventUid;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

/* OzonProducts */

#[ORM\Entity]
#[ORM\Table(name: 'ozon_products_settings')]
class OzonProductsSettings
{
    /**
     * Идентификатор сущности
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: CategoryProductUid::TYPE)]
    private CategoryProductUid $id;

    /**
     * Идентификатор события
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: OzonProductsSettingsEventUid::TYPE, unique: true)]
    private OzonProductsSettingsEventUid $event;


    public function __toString(): string
    {
        return (string)$this->id;
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

    public function setEvent(OzonProductsSettingsEvent $event): void
    {
        $this->event = $event->getId();
        $this->id = $event->getSettings();
    }
}
