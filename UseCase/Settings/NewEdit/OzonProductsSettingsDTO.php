<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Ozon\Products\UseCase\Settings\NewEdit;

use BaksDev\Ozon\Products\Api\Attribute\OzonAttributeDTO;
use BaksDev\Ozon\Products\Api\Category\OzonCategoryDTO;
use BaksDev\Ozon\Products\Entity\Settings\Event\OzonProductsSettingsEventInterface;
use BaksDev\Ozon\Products\Type\Settings\Event\OzonProductsSettingsEventUid;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see OzonProductsSettingsEventInterface */
final class OzonProductsSettingsDTO implements OzonProductsSettingsEventInterface
{
    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private ?OzonProductsSettingsEventUid $id = null;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    private ?CategoryProductUid $settings = null;

    private ?int $type = null;

    private ?int $ozon = null;

    /** Коллекция Свойств*/
    #[Assert\Valid]
    private ArrayCollection $properties;

    /** Коллекция аттрибутов*/
    #[Assert\Valid]
    private ArrayCollection $attributes;

    public function __construct()
    {

        $this->properties = new ArrayCollection();
        $this->attributes = new ArrayCollection();
    }


    /**
     * Идентификатор события
     */
    public function getEvent(): ?OzonProductsSettingsEventUid
    {
        return $this->id;
    }

    public function setId(OzonProductsSettingsEventUid $id): void
    {
        $this->id = $id;
    }

    public function getSettings(): ?CategoryProductUid
    {
        return $this->settings;
    }

    public function setSettings(?CategoryProductUid $settings): void
    {
        $this->settings = $settings;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): void
    {
        $this->type = $type;
    }

    public function getOzon(): ?int
    {
        return $this->ozon;
    }

    public function setOzon(?int $ozon): void
    {
        $this->ozon = $ozon;
    }

    public function getAttributes(): ArrayCollection
    {
        return $this->attributes;
    }

    public function getProperties(): ArrayCollection
    {
        return $this->properties;
    }

    public function addProperty(Properties\OzonProductsSettingsPropertyDTO $property): void
    {
        $filter = $this->properties->filter(function (Properties\OzonProductsSettingsPropertyDTO $element) use ($property) {
            return $property->getType()?->equals($element->getType());
        });

        if($filter->isEmpty())
        {
            $this->properties->add($property);
        }
    }

    public function removeProperty(Properties\OzonProductsSettingsPropertyDTO $property): void
    {
        $this->properties->removeElement($property);
    }

    public function addAttribute(Attributes\OzonProductsSettingsAttributeDTO $attribute): void
    {
        $filter = $this->attributes->filter(function (Attributes\OzonProductsSettingsAttributeDTO $element) use ($attribute) {
            return $attribute->getType()?->equals($element->getType());
        });

        if ($filter->isEmpty())
        {
            $this->attributes->add($attribute);
        }
    }

    public function removeAttribute(Attributes\OzonProductsSettingsAttributeDTO $attribute): void
    {
        $this->attributes->removeElement($attribute);
    }
}
