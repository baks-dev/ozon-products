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

use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Orders\Order\Messenger\OrderMessage;
use BaksDev\Orders\Order\Repository\CurrentOrderEvent\CurrentOrderEventInterface;
use BaksDev\Orders\Order\UseCase\Admin\Edit\EditOrderDTO;
use BaksDev\Orders\Order\UseCase\Admin\Edit\Products\OrderProductDTO;
use BaksDev\Ozon\Products\Messenger\Card\OzonProductsCardMessage;
use BaksDev\Ozon\Products\Messenger\Stocks\OzonProductsStocksMessage;
use BaksDev\Ozon\Repository\AllProfileToken\AllProfileOzonTokenInterface;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierInterface;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateOzonProductStocksWhenChangeOrderStatus
{
    public function __construct(
        private CurrentOrderEventInterface $currentOrderEvent,
        private CurrentProductIdentifierInterface $currentProductIdentifier,
        private AllProfileOzonTokenInterface $allProfileOzonToken,
        private MessageDispatchInterface $messageDispatch
    ) {}

    /**
     * Обновляем остатки Ozon при изменении статусов заказов
     * @see https://api-seller.ozon.ru/v1/product/import/stocks
     */
    public function __invoke(OrderMessage $message): void
    {
        /** Получаем активные токены профилей пользователя */

        $profiles = $this
            ->allProfileOzonToken
            ->onlyActiveToken()
            ->findAll();

        if($profiles->valid() === false)
        {
            return;
        }

        /** Получаем активное событие заказа */
        $OrderEvent = $this->currentOrderEvent
            ->forOrder($message->getId())
            ->execute();

        if($OrderEvent === false)
        {
            return;
        }

        $EditOrderDTO = new EditOrderDTO();
        $OrderEvent->getDto($EditOrderDTO);

        foreach($profiles as $profile)
        {
            /** @var OrderProductDTO $product */
            foreach($EditOrderDTO->getProduct() as $product)
            {
                /** Получаем идентификаторы обновляемой продукции для получения констант  */
                $ProductIdentifier = $this->currentProductIdentifier
                    ->forEvent($product->getProduct())
                    ->forOffer($product->getOffer())
                    ->forVariation($product->getVariation())
                    ->forModification($product->getModification())
                    ->find();

                if($ProductIdentifier === false)
                {
                    continue;
                }

                $OzonProductsCardMessage = new OzonProductsCardMessage(
                    new ProductUid($ProductIdentifier['id']),
                    $ProductIdentifier['offer_const'] ? new ProductOfferConst($ProductIdentifier['offer_const']) : false,
                    $ProductIdentifier['variation_const'] ? new ProductVariationConst($ProductIdentifier['variation_const']) : false,
                    $ProductIdentifier['modification_const'] ? new ProductModificationConst($ProductIdentifier['modification_const']) : false,
                    $profile
                );

                /**
                 * Добавляем в очередь обновление остатков через транспорт профиля
                 */

                $this->messageDispatch->dispatch(
                    message: new OzonProductsStocksMessage($OzonProductsCardMessage),
                    stamps: [new MessageDelay('5 seconds')], // задержка 3 сек для обновления карточки
                    transport: (string) $profile
                );
            }
        }
    }
}
