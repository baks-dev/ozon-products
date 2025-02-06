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

namespace BaksDev\Ozon\Products\Messenger\Price;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Messenger\OzonTokenMessage;
use BaksDev\Ozon\Products\Messenger\Card\OzonProductsCardMessage;
use BaksDev\Ozon\Repository\AllProfileToken\AllProfileOzonTokenInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class OzonProductsPriceUpdateByOzonTokenHandler
{
    public function __construct(
        private AllProfileOzonTokenInterface $allProfileOzonToken,
        private AllProductsIdentifierInterface $allProductsIdentifier,
        private MessageDispatchInterface $messageDispatch
    ) {}

    /**
     * Обновляем стоимость при обновлении настроек
     */
    public function __invoke(OzonTokenMessage $message): void
    {
        /** Получаем активные токены авторизации профилей Ozon */
        $profiles = $this->allProfileOzonToken
            ->onlyActiveToken()
            ->findAll();

        if(false === $profiles->valid())
        {
            return;
        }

        /* Получаем все имеющиеся карточки в системе */
        $products = $this->allProductsIdentifier->findAll();

        if($products === false || false === $products->valid())
        {
            return;
        }

        $profiles = iterator_to_array($profiles);

        foreach($products as $product)
        {
            foreach($profiles as $profile)
            {
                $OzonProductsCardMessage = new OzonProductsCardMessage(
                    $profile,
                    new ProductUid($product['product_id']),
                    $product['offer_const'] ? new ProductOfferConst($product['offer_const']) : false,
                    $product['variation_const'] ? new ProductVariationConst($product['variation_const']) : false,
                    $product['modification_const'] ? new ProductModificationConst($product['modification_const']) : false,
                );

                $OzonProductsStocksMessage = new OzonProductsPriceMessage($OzonProductsCardMessage);

                $this->messageDispatch->dispatch(
                    message: $OzonProductsStocksMessage,
                    transport: 'ozon-products'
                );
            }
        }
    }
}
