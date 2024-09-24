<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Products\Repository\Settings\AllProductsSettings;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Core\Services\Switcher\SwitcherInterface;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Ozon\Products\Entity\Settings\Event\OzonProductsSettingsEvent;
use BaksDev\Ozon\Products\Entity\Settings\OzonProductsSettings;
use BaksDev\Products\Category\Entity\Cover\CategoryProductCover;
use BaksDev\Products\Category\Entity\Event\CategoryProductEvent;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AllProductsSettingsRepository implements AllProductsSettingsInterface
{
    private PaginatorInterface $paginator;

    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        PaginatorInterface $paginator
    ) {
        $this->paginator = $paginator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    private ?SearchDTO $search = null;


    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }


    /** Метод возвращает пагинатор WbProductsSettings */
    public function findPaginator(): PaginatorInterface
    {

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal->select('settings.id');
        $dbal->addSelect('settings.event');

        //$dbal->addSelect('event.name');
        //$dbal->addSelect('event.parent');

        $dbal->from(OzonProductsSettings::class, 'settings');

        $dbal->join(
            'settings',
            OzonProductsSettingsEvent::class,
            'event',
            'event.id = settings.event AND event.settings = settings.id',
        );

        /** Категория */
        $dbal->addSelect('category.id as category_id');
        $dbal->addSelect('category.event as category_event'); /* ID события */
        $dbal->join('settings', CategoryProduct::class, 'category', 'category.id = settings.id');

        /** События категории */
        $dbal->addSelect('category_event.sort');

        $dbal->join(
            'category',
            CategoryProductEvent::class,
            'category_event',
            'category_event.id = category.event',
        );

        /** Обложка */
        $dbal->addSelect('category_cover.ext');
        $dbal->addSelect('category_cover.cdn');
        $dbal->leftJoin(
            'category_event',
            CategoryProductCover::class,
            'category_cover',
            'category_cover.event = category_event.id',
        );


        $dbal->addSelect(
            "
			CASE
			   WHEN category_cover.name IS NOT NULL THEN
					CONCAT ( '/upload/".$dbal->table(CategoryProductCover::class)."' , '/', category_cover.name)
			   ELSE NULL
			END AS cover
		"
        );


        /** Перевод категории */
        $dbal->addSelect('category_trans.name as category_name');
        $dbal->addSelect('category_trans.description as category_description');

        $dbal->leftJoin(
            'category_event',
            CategoryProductTrans::class,
            'category_trans',
            'category_trans.event = category_event.id AND category_trans.local = :local',
        );


        return $this->paginator->fetchAllAssociative($dbal);

    }
}
