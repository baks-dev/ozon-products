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

namespace BaksDev\Ozon\Products\Entity\Barcode;

use BaksDev\Core\Entity\EntityState;
use BaksDev\Ozon\Products\Entity\Barcode\Event\OzonBarcodeEvent;
use BaksDev\Ozon\Products\Type\Barcode\Event\OzonBarcodeEventUid;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'ozon_barcode')]
class OzonBarcode extends EntityState
{

    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: CategoryProductUid::TYPE)]
    private CategoryProductUid $id;

    /** ID профиля */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private UserProfileUid $profile;

    /** ID События */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: OzonBarcodeEventUid::TYPE, unique: true)]
    private OzonBarcodeEventUid $event;

    public function __construct(UserProfileUid $profile)
    {
        $this->profile = $profile;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): CategoryProductUid
    {
        return $this->id;
    }

    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }

    public function getEvent(): OzonBarcodeEventUid
    {
        return $this->event;
    }

    public function setEvent(OzonBarcodeEvent $event): void
    {
        $this->event = $event->getId();
        $this->id = $event->getMain();
    }
}