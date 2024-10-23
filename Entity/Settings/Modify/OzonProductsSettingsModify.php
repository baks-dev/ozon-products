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

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Entity\Settings\Modify;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Ip\IpAddress;
use BaksDev\Core\Type\Modify\Modify\ModifyActionNew;
use BaksDev\Core\Type\Modify\Modify\ModifyActionUpdate;
use BaksDev\Core\Type\Modify\ModifyAction;
use BaksDev\Ozon\Products\Entity\Settings\Event\OzonProductsSettingsEvent;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Модификаторы событий OzonProductsModify */

#[ORM\Entity]
#[ORM\Table(name: 'ozon_products_settings_modify')]
#[ORM\Index(columns: ['action'])]
class OzonProductsSettingsModify extends EntityEvent
{
    /** ID события */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: OzonProductsSettingsEvent::class, inversedBy: 'modify')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private OzonProductsSettingsEvent $event;

    /** Модификатор */
    #[Assert\NotBlank]
    #[ORM\Column(type: ModifyAction::TYPE)]
    private ModifyAction $action;

    /** Дата */
    #[Assert\NotBlank]
    #[ORM\Column(name: 'mod_date', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $modDate;

    /** ID пользователя  */
    #[ORM\Column(type: UserUid::TYPE, nullable: true)]
    private ?UserUid $usr = null;

    /** Ip адресс */
    #[Assert\NotBlank]
    #[ORM\Column(type: IpAddress::TYPE)]
    private IpAddress $ip;

    /** User-agent */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    private string $agent;


    public function __construct(OzonProductsSettingsEvent $event)
    {
        $this->event = $event;
        $this->modDate = new DateTimeImmutable();
        $this->ip = new IpAddress('127.0.0.1');
        $this->agent = 'console';
        $this->action = new ModifyAction(ModifyActionNew::class);
    }

    public function __clone(): void
    {
        $this->modDate = new DateTimeImmutable();
        $this->action = new ModifyAction(ModifyActionUpdate::class);
        $this->ip = new IpAddress('127.0.0.1');
        $this->agent = 'console';
    }

    public function __toString(): string
    {
        return (string) $this->event;
    }

    public function getDto($dto): mixed
    {
        if($dto instanceof OzonProductsSettingsModifyInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof OzonProductsSettingsModifyInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function upModifyAgent(IpAddress $ip, string $agent): void
    {
        $this->ip = $ip;
        $this->agent = $agent;
        $this->modDate = new DateTimeImmutable();
    }

    public function setUsr(UserUid|User|null $usr): void
    {
        $this->usr = $usr instanceof User ? $usr->getId() : $usr;
    }

    public function equals(mixed $action): bool
    {
        return $this->action->equals($action);
    }
}
