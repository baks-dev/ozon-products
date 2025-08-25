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
 *
 */

namespace BaksDev\Ozon\Products\Entity\Barcode\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Ozon\Products\Entity\Barcode\Event\Counter\OzonBarcodeCounter;
use BaksDev\Ozon\Products\Entity\Barcode\Event\Custom\OzonBarcodeCustom;
use BaksDev\Ozon\Products\Entity\Barcode\Event\Modification\OzonBarcodeModification;
use BaksDev\Ozon\Products\Entity\Barcode\Event\Modify\OzonBarcodeModify;
use BaksDev\Ozon\Products\Entity\Barcode\Event\Name\OzonBarcodeName;
use BaksDev\Ozon\Products\Entity\Barcode\Event\Offer\OzonBarcodeOffer;
use BaksDev\Ozon\Products\Entity\Barcode\Event\Property\OzonBarcodeProperty;
use BaksDev\Ozon\Products\Entity\Barcode\Event\Variation\OzonBarcodeVariation;
use BaksDev\Ozon\Products\Entity\Barcode\OzonBarcode;
use BaksDev\Ozon\Products\Type\Barcode\Event\OzonBarcodeEventUid;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'ozon_barcode_event')]
class OzonBarcodeEvent extends EntityEvent
{
    /** ID События */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: OzonBarcodeEventUid::TYPE)]
    private OzonBarcodeEventUid $id;

    /**
     * ID категории
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: CategoryProductUid::TYPE)]
    private CategoryProductUid $main;

    /** Модификатор */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: OzonBarcodeModify::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private OzonBarcodeModify $modify;

    /** Количество стикеров */
    #[ORM\OneToOne(targetEntity: OzonBarcodeCounter::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?OzonBarcodeCounter $counter = null;

    /** Флаг отображения звания в стикере */
    #[ORM\OneToOne(targetEntity: OzonBarcodeName::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?OzonBarcodeName $name = null;

    /** Добавить Торговое предложение в стикер */
    #[ORM\OneToOne(targetEntity: OzonBarcodeOffer::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?OzonBarcodeOffer $offer = null;

    /** Добавить Множественный вариант в стикер */
    #[ORM\OneToOne(targetEntity: OzonBarcodeVariation::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?OzonBarcodeVariation $variation = null;

    /** Добавить модификацию множественного варианта в стикер */
    #[ORM\OneToOne(targetEntity: OzonBarcodeModification::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?OzonBarcodeModification $modification = null;

    /** Коллекция свойств продукта */
    #[Assert\Valid]
    #[ORM\OrderBy(['sort' => 'ASC'])]
    #[ORM\OneToMany(targetEntity: OzonBarcodeProperty::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $property;

    /** Коллекция пользовательских свойств */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: OzonBarcodeCustom::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $custom;

    public function __construct()
    {
        $this->id = new OzonBarcodeEventUid();
        $this->modify = new OzonBarcodeModify($this);
    }

    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): OzonBarcodeEventUid
    {
        return $this->id;
    }

    public function getMain(): CategoryProductUid
    {
        return $this->main;
    }

    public function setMain(OzonBarcode|CategoryProductUid $main): self
    {
        if($main instanceof OzonBarcode)
        {
            return $this;
        }

        $this->main = $main;

        return $this;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof OzonBarcodeEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        if($dto instanceof OzonBarcodeEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}