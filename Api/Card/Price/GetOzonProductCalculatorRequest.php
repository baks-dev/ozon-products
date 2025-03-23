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

namespace BaksDev\Ozon\Products\Api\Card\Price;

use BaksDev\Ozon\Api\Ozon;
use BaksDev\Reference\Money\Type\Money;
use DateInterval;
use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\ItemInterface;

final class GetOzonProductCalculatorRequest extends Ozon
{
    /**  Категория */
    private int $category;

    /** Ширина, см */
    private int $width;

    /** Высота, см */
    private int $height;

    /** Длина, см */
    private int $length;

    /** Вес, кг */
    private int|float $weight;

    /** Ключ кеширования запроса */
    private string $cacheKey = '';

    /** Стоимость продукта в системе */
    private Money $price;

    /** Предать идентификатор категории от маппера */
    public function category(int $category): self
    {
        $this->category = match ($category)
        {
            17027949 => 3904, // Шины

            200000933 => 0, // Одежда
            17028741 => 1, // Столовая посуда
            41777465 => 2, // Аксессуары
        };

        $this->cacheKey .= $this->category;
        return $this;
    }

    /** Ширина, см */
    public function width(int|float $width): self
    {
        $this->width = (int) ceil($width);
        $this->cacheKey .= $this->width;
        return $this;
    }

    /** Высота, см */
    public function height(int|float $height): self
    {
        $this->height = (int) ceil($height);
        $this->cacheKey .= $this->height;
        return $this;
    }

    /** Длина, см */
    public function length(int|float $length): self
    {
        $this->length = (int) ceil($length);
        $this->cacheKey .= $this->length;
        return $this;
    }

    /** Вес, кг */
    public function weight(int|float $weight): self
    {
        $this->weight = $weight;
        $this->cacheKey .= $this->weight;
        return $this;
    }

    public function price(Money $price): self
    {
        $this->price = $price;
        $this->cacheKey .= $this->price;
        return $this;
    }

    /**
     * Получаем стоимость услуг Озон
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ImportProductsStocks
     */
    public function calc(): Money|false
    {
        /** Делаем проверку заполнения всех свойств */
        $reflect = new ReflectionClass($this);
        $properties = $reflect->getProperties(ReflectionProperty::IS_PRIVATE);

        foreach($properties as $property)
        {
            $name = $property->getName();

            if(empty($this->{$name}))
            {
                throw new InvalidArgumentException(sprintf('Invalid Argument %s', $name));
            }
        }

        $cache = new FilesystemAdapter();
        $this->cacheKey .= $this->getPercent();
        //$cache->deleteItem($this->cacheKey);

        $fbs = $cache->get(
            $this->cacheKey,
            function(ItemInterface $item): int|float|false {

                $httpClient = HttpClient::create()
                    ->withOptions(['base_uri' => 'https://calculator.ozon.ru']);

                try
                {
                    /** Присваиваем торговую наценку для расчета стоимости услуг */
                    $trade = $this->getPercent();
                    $this->price->applyString($trade);

                    $response = $httpClient->request(
                        'POST',
                        '/p-api/the-calculator-ozon-ru/api/calculate',
                        [
                            'json' => [
                                "categoryId" => 3904,
                                "price" => $this->price->getValue(), // цена
                                "dimensions" => [
                                    "length" => (string) $this->length, // длина
                                    "width" => (string) $this->width, // ширина
                                    "height" => (string) $this->height, // высота
                                ],
                                "volume" => ($this->width * $this->height * $this->length / 1000), // объем, литры // 108 000  = 108
                                "weight" => $this->weight, // вес

                                "acceptance" => [
                                    "type" => "dropOff",
                                    "acceptanceType" => "ozon",
                                    "postingsPerShipment" => 1,
                                    "dropOffType" => "pickUpPoint"
                                ]
                            ]
                        ]
                    );

                    $content = $response->toArray(false);

                }
                catch(Exception)
                {
                    $item->expiresAfter(DateInterval::createFromDateString('1 second'));
                    return false;
                }

                /** Получаем стоимость услуг FBS */
                $item->expiresAfter(DateInterval::createFromDateString('1 day'));
                return abs($content['fbs']['totalOzonServicesPerItem']);
            }
        );

        if($fbs === false)
        {
            return false;
        }

        $services = new Money($fbs);
        $this->price->add($services);

        return $this->price;

    }
}
