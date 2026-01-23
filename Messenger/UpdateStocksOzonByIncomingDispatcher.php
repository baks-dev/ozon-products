<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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
use BaksDev\Ozon\Products\Messenger\Card\OzonProductsCardMessage;
use BaksDev\Ozon\Products\Messenger\Stocks\OzonProductsStocksMessage;
use BaksDev\Ozon\Repository\AllProfileToken\AllProfileOzonTokenInterface;
use BaksDev\Products\Stocks\Entity\Stock\Event\ProductStockEvent;
use BaksDev\Products\Stocks\Messenger\ProductStockMessage;
use BaksDev\Products\Stocks\Repository\ProductStocksEvent\ProductStocksEventInterface;
use BaksDev\Products\Stocks\Type\Status\ProductStockStatus\ProductStockStatusIncoming;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обновляет складские остатки Ozon при поступлении продукции на склад
 */
#[AsMessageHandler(priority: 0)]
final readonly class UpdateStocksOzonByIncomingDispatcher
{
    public function __construct(
        #[Target('ozonProductsLogger')] private LoggerInterface $logger,
        private ProductStocksEventInterface $ProductStocksEventRepository,
        private MessageDispatchInterface $messageDispatch,
        private AllProfileOzonTokenInterface $allProfileOzonToken,
    ) {}


    public function __invoke(ProductStockMessage $message): void
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

        /** Получаем активное событие складской заявки */

        $ProductStockEvent = $this->ProductStocksEventRepository
            ->forEvent($message->getEvent())
            ->find();

        if(false === ($ProductStockEvent instanceof ProductStockEvent))
        {
            return;
        }

        /**
         * Если Статус заявки не является Incoming «Приход на склад»
         */
        if(false === $ProductStockEvent->equalsProductStockStatus(ProductStockStatusIncoming::class))
        {
            return;
        }

        // Получаем всю продукцию в ордере со статусом Incoming
        $products = $ProductStockEvent->getProduct();

        if($products->isEmpty())
        {
            $this->logger->warning('Заявка не имеет продукции в коллекции', [self::class.':'.__LINE__]);
            return;
        }

        foreach($profiles as $UserProfileUid)
        {
            foreach($products as $ProductStockProduct)
            {
                $OzonProductsCardMessage = new OzonProductsCardMessage(
                    $UserProfileUid,
                    $ProductStockProduct->getProduct(),
                    $ProductStockProduct->getOffer(),
                    $ProductStockProduct->getVariation(),
                    $ProductStockProduct->getModification(),
                );

                /**
                 * Добавляем в очередь обновление остатков через транспорт профиля
                 */

                $this->messageDispatch->dispatch(
                    message: new OzonProductsStocksMessage($OzonProductsCardMessage),
                    stamps: [new MessageDelay('5 seconds')],
                    transport: $UserProfileUid.'-low',
                );
            }
        }
    }
}
