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
 */

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Repository\Settings\OzonProductsSettingsCurrentEvent;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Ozon\Products\Entity\Settings\Event\OzonProductsSettingsEvent;
use BaksDev\Ozon\Products\Entity\Settings\OzonProductsSettings;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final readonly class OzonProductsSettingsCurrentEventRepository implements OzonProductsSettingsCurrentEventInterface
{
    public function __construct(private ORMQueryBuilder $ORMQueryBuilder) {}

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
            ->setParameter(
                key: 'profile',
                value: $profile,
                type: UserProfileUid::TYPE
            );


        $orm
            ->select('event')
            ->join(
                OzonProductsSettingsEvent::class,
                'event',
                'WITH',
                'event.id = main.event'
            );

        return $orm->getOneOrNullResult() ?: false;
    }
}
