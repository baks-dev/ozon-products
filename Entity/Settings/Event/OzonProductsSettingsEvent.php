<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Products\Entity\Settings\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Ozon\Products\Entity\Settings\Attribute\OzonProductsSettingsAttribute;
use BaksDev\Ozon\Products\Entity\Settings\Modify\OzonProductsSettingsModify;
use BaksDev\Ozon\Products\Entity\Settings\OzonProductsSettings;
use BaksDev\Ozon\Products\Entity\Settings\Property\OzonProductsSettingsProperty;
use BaksDev\Ozon\Products\Type\Settings\Event\OzonProductsSettingsEventUid;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* OzonProductsEvent */

#[ORM\Entity]
#[ORM\Table(name: 'ozon_products_settings_event')]
class OzonProductsSettingsEvent extends EntityEvent
{
    /**
     * Идентификатор события
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: OzonProductsSettingsEventUid::TYPE)]
    private OzonProductsSettingsEventUid $id;

    /**
     * Идентификатор категории в системе
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: CategoryProductUid::TYPE)]
    private CategoryProductUid $settings;

    /**
     * Идентификатор категории на Ozon
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::INTEGER)]
    private int $ozon;


    /**
     * Идентификатор типа на Ozon
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::INTEGER)]
    private int $type;


    /**
     * Модификатор
     */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: OzonProductsSettingsModify::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private OzonProductsSettingsModify $modify;

    /**
     * Свойства карточки Ozon
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: OzonProductsSettingsProperty::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $properties;


    /**
     * Параметры продукции категории Ozon
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: OzonProductsSettingsAttribute::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $attributes;


    public function __construct()
    {
        $this->id = new OzonProductsSettingsEventUid();
        $this->modify = new OzonProductsSettingsModify($this);
    }

    public function __clone(): void
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof OzonProductsSettingsEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        if($dto instanceof OzonProductsSettingsEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setMain(OzonProductsSettings $settings): self
    {
        return $this;
    }

    public function getId(): OzonProductsSettingsEventUid
    {
        return $this->id;
    }

    public function getSettings(): CategoryProductUid
    {
        return $this->settings;
    }


}
