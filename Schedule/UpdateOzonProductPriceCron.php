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

namespace BaksDev\Ozon\Products\Schedule;

use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Products\Messenger\Card\OzonProductsCardMessage;
use BaksDev\Ozon\Products\Messenger\Price\OzonProductsPriceMessage;
use BaksDev\Ozon\Products\Messenger\Stocks\OzonProductsStocksUpdateDispatcher;
use BaksDev\Ozon\Repository\AllProfileToken\AllProfileOzonTokenInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

/** @see YaMarketProductsPriceUpdate */

/**
 * Обновляем цены
 *
 * #midnight - каждый день между 00:00 и 2:59
 *
 * @see https://symfony.com/doc/current/scheduler.html#cron-expression-triggers
 * @see OzonProductsStocksUpdateDispatcher
 */
#[AsCronTask('#midnight', jitter: 60)]
final readonly class UpdateOzonProductPriceCron
{
    public function __construct(
        #[Target('ozonProductsLogger')] private LoggerInterface $logger,
        private MessageDispatchInterface $messageDispatch,
        private AllProfileOzonTokenInterface $AllProfileOzonTokenRepository,
        private AllProductsIdentifierInterface $AllProductsIdentifierRepository,
    ) {}

    public function __invoke(): void
    {
        /** Получаем все активные профили, у которых активный токен  */
        $profiles = $this->AllProfileOzonTokenRepository
            ->onlyActiveToken()
            ->findAll();

        if(false === $profiles || false === $profiles->valid())
        {
            $this->logger->warning(
                'Профили с активными токенами Яндекс не найдены',
                [__FILE__.':'.__LINE__],
            );

            return;
        }

        foreach($profiles as $stamps => $UserProfileUid)
        {
            /* Получаем все имеющиеся карточки в системе */
            $products = $this->AllProductsIdentifierRepository
                ->forProfile($UserProfileUid)
                ->findAll();

            if(false === $products || false === $products->valid())
            {
                $this->logger->warning(
                    'Карточек для обновления не найдено',
                    [__FILE__.':'.__LINE__],
                );

                continue;
            }

            foreach($products as $ProductsIdentifierResult)
            {
                $OzonProductsCardMessage = new OzonProductsCardMessage(
                    $UserProfileUid,
                    $ProductsIdentifierResult->getProductId(),
                    $ProductsIdentifierResult->getProductOfferConst(),
                    $ProductsIdentifierResult->getProductVariationConst(),
                    $ProductsIdentifierResult->getProductModificationConst(),
                );

                $OzonProductsStocksMessage = new OzonProductsPriceMessage($OzonProductsCardMessage);

                /** Консольную комманду выполняем синхронно */
                $this->messageDispatch->dispatch(
                    message: $OzonProductsStocksMessage,
                    stamps: [new MessageDelay(sprintf('%s minutes', $stamps))],
                    transport: $UserProfileUid.'-low',
                );
            }
        }
    }
}