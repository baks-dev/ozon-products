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

namespace BaksDev\Ozon\Products\Messenger\Price;

use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Products\Api\Card\Price\GetOzonProductCalculatorRequest;
use BaksDev\Ozon\Products\Api\Card\Price\UpdateOzonProductPriceRequest;
use BaksDev\Ozon\Products\Mapper\OzonProductsMapper;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Reference\Money\Type\Money;
use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final class OzonProductsPriceUpdate
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly UpdateOzonProductPriceRequest $updateOzonProductPriceRequest,
        private readonly OzonProductsMapper $itemOzonProducts,
        private readonly ProductsOzonCardInterface $ozonProductsCard,
        private readonly MessageDispatchInterface $messageDispatch,
        private readonly GetOzonProductCalculatorRequest $GetOzonProductCalculatorRequest,
        LoggerInterface $ozonProductsLogger,
    )
    {
        $this->logger = $ozonProductsLogger;
    }

    /**
     * Обновляем остатки товаров Ozon
     */
    public function __invoke(OzonProductsPriceMessage $message): void
    {
        $product = $this->ozonProductsCard
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->find();

        if($product === false)
        {
            return;
        }

        /** Не обновляем стоимость без цены */
        if(empty($product['product_price']))
        {
            return;
        }

        /** Присваиваем профиль пользователя для всех Request запросов  */
        $this->updateOzonProductPriceRequest->profile($message->getProfile());

        $Card = $this->itemOzonProducts->getData($product);


        /** Не обновляем стоимость без параметров упаковки */
        if(
            empty($Card['width']) ||
            empty($Card['height']) ||
            empty($Card['depth']) ||
            empty($Card['weight'])
        )
        {
            return;
        }


        /** Получаем стоимость услуг и присваиваем полную стоимость */

        $price = $this->GetOzonProductCalculatorRequest
            ->category($Card['description_category_id'])
            ->width($Card['width'] / 10)
            ->height($Card['height'] / 10)
            ->length($Card['depth'] / 10)
            ->weight($Card['weight'] / 1000)
            ->price(new Money($Card['price']))
            ->calc();

        /** Если произошла временная ошибка калькулятора - пробуем позже */
        if($price === false)
        {
            $this->messageDispatch->dispatch(
                message: $message,
                stamps: [new MessageDelay(DateInterval::createFromDateString('5 seconds'))], // отложенная на 5 секунд
                transport: 'ozon-products'
            );

            return;
        }


        /** Обновляем стоимость */

        $result = $this->updateOzonProductPriceRequest
            ->price($price)
            ->article($Card['offer_id'])
            ->update();

        if($result === false)
        {
            return;
        }

        $this->logger->info('Обновили стоимость {article}: {rice}', [
            'article' => $Card['offer_id'],
            'rice' => $price->getValue(),
            'profile' => $message->getProfile()
        ]);

    }
}
