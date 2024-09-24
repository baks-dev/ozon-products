<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Repository\Settings\OzonProductsSettingsCurrentEvent;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Ozon\Products\Entity\Settings\Event\OzonProductsSettingsEvent;
use BaksDev\Ozon\Products\Entity\Settings\OzonProductsSettings;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class OzonProductsSettingsCurrentEventRepository implements OzonProductsSettingsCurrentEventInterface
{
    public function __construct(
        private readonly ORMQueryBuilder $ORMQueryBuilder
    ) {
    }

    /** Метод возвращает активное событие настройки профиля */
    public function findByProfile(UserProfileUid|string $profile): OzonProductsSettingsEvent|false
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);


        $orm
            ->from(OzonProductsSettings::class, 'main')
            ->where('main.id = :profile')
            ->setParameter('profile', $profile, UserProfileUid::TYPE);


        $orm
            ->select('event')
            ->join(
                OzonProductsSettingsEvent::class,
                'event',
                'WITH',
                'event.id = main.event'
            );

        return $orm->getQuery()->getOneOrNullResult() ?: false;
    }
}
