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

namespace BaksDev\Ozon\Products\Form\Preform;

use BaksDev\Ozon\Products\Mapper\Category\OzonProductsCategoryInterface;
use BaksDev\Ozon\Products\Mapper\Type\OzonProductsTypeInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Symfony\Component\Validator\Constraints as Assert;

final class PreformDTO
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private ?CategoryProductUid $category = null;

    #[Assert\NotBlank]
    private ?OzonProductsCategoryInterface $ozon;

    private ?OzonProductsTypeInterface $type = null;

    public function getCategory(): ?CategoryProductUid
    {
        return $this->category;
    }

    public function setCategory(?CategoryProductUid $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getOzon(): ?OzonProductsCategoryInterface
    {
        return $this->ozon;
    }

    public function setOzon(?OzonProductsCategoryInterface $ozon): void
    {
        $this->ozon = $ozon;
    }

    public function getType(): ?OzonProductsTypeInterface
    {
        return $this->type;
    }

    public function setType(?OzonProductsTypeInterface $type): self
    {
        $this->type = $type;
        return $this;
    }

}
