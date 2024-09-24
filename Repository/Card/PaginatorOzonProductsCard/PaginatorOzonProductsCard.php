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

namespace BaksDev\Ozon\Products\Repository\Card\PaginatorOzonProductsCard;

use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterDTO;
use BaksDev\Yandex\Market\Products\Entity\Card\YaMarketProductsCard;

final class PaginatorOzonProductsCard implements PaginatorOzonProductsCardInterface
{
    private PaginatorInterface $paginator;

    private DBALQueryBuilder $DBALQueryBuilder;

    private ?SearchDTO $search = null;

    private ?ProductFilterDTO $filter = null;


    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        PaginatorInterface $paginator,
    ) {
        $this->paginator = $paginator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }

    public function filter(ProductFilterDTO $filter): static
    {
        $this->filter = $filter;
        return $this;
    }

    /** Метод возвращает пагинатор OzonProductsCard */
    public function findAll(): PaginatorInterface
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal->select('*');
        $dbal->from(YaMarketProductsCard::class, 'ya_market_products_card');

        /* Поиск */
        if($this->search->getQuery())
        {
            //            $dbal
            //                ->createSearchQueryBuilder($this->search)
            //                //->addSearchEqualUid('account.id')
            //
            //                ->addSearchLike('code.code')
            //            ;

        }

        return $this->paginator->fetchAllAssociative($dbal);
    }
}
