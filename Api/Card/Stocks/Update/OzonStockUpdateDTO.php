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

namespace BaksDev\Ozon\Products\Api\Card\Stocks\Update;

final readonly class OzonStockUpdateDTO
{
    /** Идентификатор товара. */
    private int $product;

    /** Идентификатор товара в системе продавца — артикул. */
    private string $offer;

    /**  Если информации о товаре успешно обновлена — true. */
    private bool $updated;

    /** Массив ошибок */
    private array $errors;

    public function __construct(array $data)
    {
        $this->product = $data['product_id'];
        $this->offer = $data['offer_id'];
        $this->updated = $data['updated'];
        $this->errors = $data['errors'];
    }

    public function getProductId(): int
    {
        return $this->product;
    }

    public function getOfferId(): string
    {
        return $this->offer;
    }

    public function updated(): bool
    {
        return $this->updated;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

}
