<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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
        $filter = $this->properties->filter(function(Properties\OzonProductsSettingsPropertyDTO $element) use ($property
        ) {
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
        $filter = $this->attributes->filter(function(Attributes\OzonProductsSettingsAttributeDTO $element) use (
            $attribute
        ) {
            return $attribute->getType()?->equals($element->getType());
        });

        if($filter->isEmpty())
        {
            $this->attributes->add($attribute);
        }
    }

    public function removeAttribute(Attributes\OzonProductsSettingsAttributeDTO $attribute): void
    {
        $this->attributes->removeElement($attribute);
    }
}
