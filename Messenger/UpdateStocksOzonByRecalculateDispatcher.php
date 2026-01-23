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
use BaksDev\Products\Stocks\Messenger\Products\Recalculate\RecalculateProductMessage;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsCardMessage;
use BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsStocksUpdate\YaMarketProductsStocksMessage;
use BaksDev\Yandex\Market\Repository\AllProfileToken\AllProfileYaMarketTokenInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Отправляем сообщение на обновление остатков при обновлении складского учета
 */
#[AsMessageHandler(priority: 0)]
final readonly class UpdateStocksOzonByRecalculateDispatcher
{
    public function __construct(
        private AllProfileYaMarketTokenInterface $allProfileYaMarketToken,
        private AllProfileOzonTokenInterface $allProfileOzonToken,
        private MessageDispatchInterface $messageDispatch
    ) {}


    public function __invoke(RecalculateProductMessage $product): void
    {
        /**  Получаем активные токены профилей пользователя */

        $profiles = $this->allProfileYaMarketToken
            ->onlyActiveToken()
            ->findAll();

        if($profiles->valid() === false)
        {
            return;
        }

        foreach($profiles as $UserProfileUid)
        {
            $OzonProductsCardMessage = new OzonProductsCardMessage(
                $UserProfileUid,
                $product->getProduct(),
                $product->getOffer(),
                $product->getVariation(),
                $product->getModification(),
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
