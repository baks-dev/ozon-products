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

namespace BaksDev\Ozon\Products\UseCase\Settings\Delete;

use BaksDev\Ozon\Products\Entity\Settings\Event\OzonProductsSettingsEventInterface;
use BaksDev\Ozon\Products\Type\Settings\Event\OzonProductsSettingsEventUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ozonProductsSettingsEvent */
final class DeleteOzonProductsSettingsDTO implements OzonProductsSettingsEventInterface
{
    /**
     * Идентификатор события
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private ?OzonProductsSettingsEventUid $id = null;

    /**
     * Модификатор
     */
    #[Assert\Valid]
    private Modify\ModifyDTO $modify;

    public function __construct()
    {
        $this->modify = new Modify\ModifyDTO();
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


    /**
     * Модификатор
     */
    public function getModify(): Modify\ModifyDTO
    {
        return $this->modify;
    }
}
