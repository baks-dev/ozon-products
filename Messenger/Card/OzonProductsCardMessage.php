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

namespace BaksDev\Ozon\Products\Messenger\Card;

use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class OzonProductsCardMessage
{
    /** ID продукта*/
    private ProductUid $product;

    /** Постоянный уникальный идентификатор ТП*/
    private ProductOfferConst|false $offerConst;

    /** Постоянный уникальный идентификатор варианта */
    private ProductVariationConst|false $offerVariation;

    /** Постоянный уникальный идентификатор модификации */
    private ProductModificationConst|false $offerModification;

    /** Идентификатор профиля пользователя */
    private UserProfileUid $profile;

    public function __construct(
        ProductUid $product,
        ProductOfferConst|false $offerConst,
        ProductVariationConst|false $offerVariation,
        ProductModificationConst|false $offerModification,
        UserProfileUid $profile,
    )
    {
        $this->profile = $profile;
        $this->product = $product;
        $this->offerConst = $offerConst;
        $this->offerVariation = $offerVariation;
        $this->offerModification = $offerModification;
    }

    /**  Product */
    public function getProduct(): ProductUid
    {
        return $this->product;
    }

    /**  OfferConst */
    public function getOfferConst(): ProductOfferConst|false
    {
        return $this->offerConst;
    }

    /**  OfferVariation */
    public function getOfferVariation(): ProductVariationConst|false
    {
        return $this->offerVariation;
    }

    /**  OfferModification */
    public function getOfferModification(): ProductModificationConst|false
    {
        return $this->offerModification;
    }

    /**  Profile */
    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }
}
