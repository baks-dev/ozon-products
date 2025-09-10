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

namespace BaksDev\Ozon\Products\Messenger\Card\Result;

use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Products\Api\Card\Update\GetOzonCardStatusUpdateRequest;
use BaksDev\Ozon\Products\Messenger\Card\OzonProductsCardMessage;
use BaksDev\Ozon\Products\Messenger\Price\OzonProductsPriceMessage;
use BaksDev\Products\Product\Repository\CurrentProductByArticle\ProductConstByArticleInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** Проверка выполнения задания */
#[AsMessageHandler(priority: 0)]
final readonly class ResultOzonProductsCardDispatcher
{
    public function __construct(
        #[Target('ozonProductsLogger')] private LoggerInterface $logger,
        private GetOzonCardStatusUpdateRequest $cardUpdateResultRequest,
        private ProductConstByArticleInterface $productConstByArticle,
        private MessageDispatchInterface $messageDispatch,
    ) {}

    public function __invoke(ResultOzonProductsCardMessage $message): void
    {
        $result = $this->cardUpdateResultRequest
            ->forTokenIdentifier($message->getToken())
            ->get($message->getId());

        /** false - если задание не найдено */
        if(false === $result || empty($result['errors']))
        {
            return;
        }

        foreach($result['errors'] as $error)
        {
            /** Если ошибка вызвана обновление цены */
            if(isset($error['field']) && $error['field'] === 'price')
            {
                $product = $this->productConstByArticle->find($result['offer_id']);

                $OzonProductsCardMessage = new OzonProductsCardMessage(
                    $message->getProfile(),
                    $product->getProduct(),
                    $product->getOfferConst(),
                    $product->getVariationConst(),
                    $product->getModificationConst(),
                );

                /** Выполняем запрос на обновление цены */
                $OzonProductsPriceMessage = new OzonProductsPriceMessage($OzonProductsCardMessage);

                $this->messageDispatch->dispatch(
                    message: $OzonProductsPriceMessage,
                    transport: 'ozon-products',
                );

                /** Повторно выполняем обновление карточки */
                $this->messageDispatch->dispatch(
                    message: $OzonProductsCardMessage,
                    stamps: [new MessageDelay('5 seconds')],
                    transport: 'ozon-products',
                );

                $this->logger->critical(
                    sprintf('ozon-products: Ошибка обновления стоимости карточки %s. Пробуем обновить повторно через 5 сек.', $result['offer_id']),
                    $error,
                );

                return;
            }

            $this->logger->critical(
                sprintf('ozon-products: Ошибка обновления карточки %s', $result['offer_id']),
                $error,
            );
        }
    }
}
