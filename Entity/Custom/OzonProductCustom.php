<?php
/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Products\Entity\Custom;

use BaksDev\Core\Entity\EntityState;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaksDev\Ozon\Products\Entity\Custom\Images\OzonProductCustomImage;

#[ORM\Entity]
#[ORM\Table(name: 'ozon_product_custom')]
final class OzonProductCustom extends EntityState
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ProductInvariableUid::TYPE)]
    private ProductInvariableUid $invariable;

    /** Коллекция изображений продукта для Озона */
    #[ORM\OrderBy(['root' => 'DESC'])]
    #[ORM\OneToMany(targetEntity: OzonProductCustomImage::class, mappedBy: 'invariable', cascade: ['all'], fetch: 'EAGER')]
    private Collection $images;

    public function __construct(ProductInvariableUid $invariable)
    {
        $this->invariable = $invariable;
        $this->images = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->invariable;
    }

    public function getId(): ProductInvariableUid
    {
        return $this->invariable;
    }

    /** Метод возвращает коллекцию изображений для сжатия в формат WEBP */
    public function getImages(): Collection
    {
        return $this->images;
    }
}