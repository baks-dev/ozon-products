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
use BaksDev\Ozon\Orders\Type\ProfileType\TypeProfileFbsOzon;
use BaksDev\Reference\Money\Type\Money;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\ItemInterface;

final class GetOzonProductCalculatorRequest extends Ozon
{
    /** Ширина, см */
    private int $width;

    /** Высота, см */
    private int $height;

    /** Длина, см */
    private int $length;

    /** Вес, кг */
    private int|float $weight;

    /** Стоимость продукта в системе */
    private Money $price;

    /** Ширина, см */
    public function width(int|float $width): self
    {
        $this->width = (int) ceil($width);
        return $this;
    }

    /** Высота, см */
    public function height(int|float $height): self
    {
        $this->height = (int) ceil($height);
        return $this;
    }

    /** Длина, см */
    public function length(int|float $length): self
    {
        $this->length = (int) ceil($length);
        return $this;
    }

    /** Вес, кг */
    public function weight(int|float $weight): self
    {
        $this->weight = $weight;
        return $this;
    }

    public function price(Money $price): self
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Расчет стоимости услуг Озон
     */
    public function calc(): Money|false
    {
        $debug = null;

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

        $debug['Первоначальная стоимость продукта'] = $this->price;

        /**
         * Присваиваем торговую наценку для расчета стоимости услуг
         */
        $trade = $this->getPercent();
        $this->price->applyString($trade);
        $startPrice = $this->price->getValue();
        $debug['После процента токена'] = $this->price;


        $this->getType()->equals(TypeProfileFbsOzon::class);

        if($this->getType()->equals(TypeProfileFbsOzon::class))
        {

        }

        /**
         * Вознаграждение   FBO 8%     FBS 10%
         */
        $offset = $this->price->percent(10);
        $debug['Вознаграждение'] = $offset;


        /**
         * Эквайринг 1.9% (округляем до 2-х)
         */
        $eqv = $this->price->percent(2);
        $debug['Эквайринг'] = $eqv;


        /**
         * Стоимость логистики (объем, литры)
         *
         * @see https://seller-edu.ozon.ru/commissions-tariffs/commissions-tariffs-ozon/rashody-na-dostavku#fbs
         */

        $volume = ($this->width * $this->height * $this->length / 1000);

        // Логистика FBS
        $logistics = match (true)
        {
            // до 0,4 литра включительно — 43 ₽;
            $volume <= 0.4 => 43,

            // свыше 0,4 литра до 1 литра включительно — 76 ₽;
            $volume <= 1 => 76,

            // до 190 литров включительно — 18 ₽ за каждый дополнительный литр свыше объёма 1 л;
            $volume <= 190 => (76 + (ceil($volume - 1) * 18)),

            // свыше 190 литров — 3478 ₽.
            default => 3478
        };


        $debug['Логистика'] = '('.$this->width.'x'.$this->height.'x'.$this->length.') '.$logistics;


        $this->price->add($offset); // Вознаграждение

        $this->price->add($eqv); // Эквайринг

        $this->price->add(new Money($logistics)); // Логистика

        $this->price->add(new Money(500)); // Последняя миля - 500 руб
        $debug['Последняя миля'] = 500;

        $this->price->add(new Money(30)); // Обработка отправления - 30 руб
        $debug['Обработка отправления'] = 30;


        //$debug['Итого затраты на логистику'] = ($this->price->getValue() - $startPrice);
        //dd($debug);

        return $this->price;

    }
}
