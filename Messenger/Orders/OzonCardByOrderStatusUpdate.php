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

namespace BaksDev\Ozon\Products\Messenger\Orders;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Orders\Order\Messenger\OrderMessage;
use BaksDev\Orders\Order\Repository\CurrentOrderEvent\CurrentOrderEventInterface;
use BaksDev\Orders\Order\UseCase\Admin\Edit\EditOrderDTO;
use BaksDev\Orders\Order\UseCase\Admin\Edit\Products\OrderProductDTO;
use BaksDev\Ozon\Products\Messenger\Card\OzonProductsCardMessage;
use BaksDev\Ozon\Products\Messenger\Stocks\OzonProductsStocksMessage;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierInterface;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsCardMessage;
use BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsStocksUpdate\YaMarketProductsStocksMessage;
use BaksDev\Yandex\Market\Repository\AllProfileToken\AllProfileYaMarketTokenInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final class OzonCardByOrderStatusUpdate
{
    public function __construct(
        private readonly CurrentOrderEventInterface $currentOrderEvent,
        private readonly CurrentProductIdentifierInterface $currentProductIdentifier,
        private readonly AllProfileYaMarketTokenInterface $allProfileYaMarketToken,
        private readonly MessageDispatchInterface $messageDispatch,
    ) {
    }

    /**
     * Обновляем остатки Ozon при изменении статусов заказов
     */
    public function __invoke(OrderMessage $message): void
    {
        /**  Получаем активные токены профилей пользователя */

        $profiles = $this
            ->allProfileYaMarketToken
            ->onlyActiveToken()
            ->findAll();


        if($profiles->valid() === false)
        {
            return;
        }


        /** Получаем событие заказа */
        $OrderEvent = $this->currentOrderEvent->forOrder($message->getId())->find();

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

                $ozonProductsCardMessage = new OzonProductsCardMessage(
                    $profile,
                    $ProductIdentifier['id'],
                    $ProductIdentifier['offer_const'],
                    $ProductIdentifier['variation_const'],
                    $ProductIdentifier['modification_const']
                );

                /** Добавляем в очередь обновление остатков через транспорт профиля */
                $this->messageDispatch->dispatch(
                    message: new OzonProductsStocksMessage($ozonProductsCardMessage),
                    stamps: [new DelayStamp(3000)], // задержка 3 сек для обновления карточки
                    transport: (string) $profile
                );
            }
        }
    }
}
