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

namespace BaksDev\Ozon\Products\Messenger;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Products\Messenger\Card\OzonProductsCardMessage;
use BaksDev\Ozon\Repository\AllProfileToken\AllProfileOzonTokenInterface;
use BaksDev\Products\Product\Messenger\ProductMessage;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateOzonProductCardWhenChangeProduct
{
    public function __construct(
        private AllProductsIdentifierInterface $allProductsIdentifier,
        private AllProfileOzonTokenInterface $allProfileOzonToken,
        private MessageDispatchInterface $messageDispatch,
    ) {}

    /**
     * Обновляем карточку Озон при изменении системной карточки
     */
    public function __invoke(ProductMessage $message): void
    {
        /**  Получаем активные токены профилей пользователя */
        $profiles = $this
            ->allProfileOzonToken
            ->onlyActiveToken()
            ->findAll();

        if($profiles->valid() === false)
        {
            return;
        }

        foreach($profiles as $profile)
        {
            /** Получаем идентификаторы обновляемой продукции  */
            $products = $this
                ->allProductsIdentifier
                ->forProduct($message->getId())
                ->findAll();

            if($products === false)
            {
                return;
            }

            foreach($products as $product)
            {
                $OzonProductsCardMessage = new OzonProductsCardMessage(
                    new ProductUid($product['product_id']),
                    $product['offer_const'] ? new ProductOfferConst($product['offer_const']) : false,
                    $product['variation_const'] ? new ProductVariationConst($product['variation_const']) : false,
                    $product['modification_const'] ? new ProductModificationConst($product['modification_const']) : false,
                    $profile
                );

                /** Транспорт ozon-products чтобы не мешать общей очереди */
                $this->messageDispatch->dispatch(
                    message: $OzonProductsCardMessage,
                    transport: 'ozon-products'
                );
            }
        }
    }
}
