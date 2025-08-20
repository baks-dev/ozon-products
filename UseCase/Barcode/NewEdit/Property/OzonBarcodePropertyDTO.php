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

namespace BaksDev\Ozon\Products\UseCase\Barcode\NewEdit\Property;

use BaksDev\Ozon\Products\Entity\Barcode\Event\Property\OzonBarcodePropertyInterface;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see OzonBarcodeProperty */
final class OzonBarcodePropertyDTO implements OzonBarcodePropertyInterface
{
    /**
     * Сортировка
     */
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 999)]
    private ?int $sort = 100;

    /**
     * Название поля
     */
    #[Assert\NotBlank]
    private ?string $name = null;

    /**
     * ID свойства продукта в категории
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private CategoryProductSectionFieldUid $offer;

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(?int $sort): void
    {
        $this->sort = $sort;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getOffer(): CategoryProductSectionFieldUid
    {
        return $this->offer;
    }

    public function setOffer(CategoryProductSectionFieldUid $offer): void
    {
        $this->offer = $offer;
    }
}