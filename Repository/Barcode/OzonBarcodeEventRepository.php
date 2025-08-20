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

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Repository\Barcode;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Ozon\Products\Entity\Barcode\Event\OzonBarcodeEvent;
use BaksDev\Ozon\Products\Entity\Barcode\OzonBarcode;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;

final class OzonBarcodeEventRepository
{
    private ORMQueryBuilder $ORMQueryBuilder;

    public function __construct(ORMQueryBuilder $ORMQueryBuilder)
    {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }

    public function getOzonBarcodeEventByCategory(CategoryProductUid $category): ?OzonBarcodeEvent
    {
        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(OzonBarcode::class, 'barcode')
            ->where('barcode.id = :category')
            ->setParameter(
                key: 'category',
                value: $category,
                type: CategoryProductUid::TYPE
            );

        $qb
            ->select('event')
            ->leftJoin(OzonBarcodeEvent::class,
                'event',
                'WITH',
                'event.id = barcode.event'
            );

        return $qb->getOneOrNullResult();
    }
}