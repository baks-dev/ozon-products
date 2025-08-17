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

namespace BaksDev\Ozon\Products\Repository\OzonProductImageIdentifierByName;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Ozon\Products\Entity\Custom\Images\OzonProductCustomImage;
use BaksDev\Ozon\Products\Entity\Custom\OzonProductCustom;
use BaksDev\Ozon\Products\Type\Custom\Image\OzonProductImageUid;

final readonly class OzonProductImageIdentifierByNameRepository implements OzonProductImageIdentifierByNameInterface
{
    public function __construct(private DBALQueryBuilder $DBALQueryBuilder) {}

    /** Метод возвращает идентификатор файла изображения по дайджесту md5 (image.name) */
    public function find(string $name): OzonProductImageUid|false
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('image.id AS value')
            ->from(OzonProductCustomImage::class, 'image')
            ->where('image.name = :name')
            ->setParameter(
                key: 'name',
                value: $name,
            );

        $dbal->join(
            'image',
            OzonProductCustom::class,
            'custom',
            'image.invariable = custom.invariable',
        );

        return $dbal->fetchHydrate(OzonProductImageUid::class);
    }
}