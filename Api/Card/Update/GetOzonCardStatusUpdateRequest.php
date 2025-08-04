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

namespace BaksDev\Ozon\Products\Api\Card\Update;

use App\Kernel;
use BaksDev\Ozon\Api\Ozon;

final class GetOzonCardStatusUpdateRequest extends Ozon
{
    /**
     * Узнать статус добавления товара
     * @see https://api-seller.ozon.ru/v1/product/import/info
     */
    public function get(int|string $task): array|false
    {
        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                '/v1/product/import/info',
                [
                    "json" => ['task_id' => (string) $task]
                ]
            );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            if($content['code'] === 'task not found id')
            {
                return false;
            }

            $this->logger->critical(
                sprintf('ozon-products: Ошибка %s при получении задания на обновление карточки', $response->getStatusCode()),
                [self::class.':'.__LINE__, $content],
            );

            return false;
        }

        if(false === isset($content['result']))
        {
            return false;
        }

        $result = current($content['result']);

        return current($result);
    }
}
