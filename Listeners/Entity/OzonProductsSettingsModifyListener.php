<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Listeners\Entity;

use BaksDev\Core\Type\Ip\IpAddress;
use BaksDev\Ozon\Products\Entity\Settings\Modify\OzonProductsSettingsModify;
use BaksDev\Users\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: OzonProductsSettingsModify::class)]
final class OzonProductsSettingsModifyListener
{
    public function __construct(
        private readonly RequestStack $request,
        private readonly TokenStorageInterface $token,
    ) {}

    public function prePersist(OzonProductsSettingsModify $data, LifecycleEventArgs $event): void
    {
        $token = $this->token->getToken();

        if($token)
        {
            $data->setUsr($token->getUser());

            if($token instanceof SwitchUserToken)
            {
                /** @var User $originalUser */
                $originalUser = $token->getOriginalToken()->getUser();
                $data->setUsr($originalUser);
            }
        }

        /* Если пользователь не из консоли */
        if($this->request->getCurrentRequest())
        {
            $data->upModifyAgent(
                new IpAddress($this->request->getCurrentRequest()->getClientIp()), /* Ip */
                $this->request->getCurrentRequest()->headers->get('User-Agent') /* User-Agent */
            );
        }
    }
}
