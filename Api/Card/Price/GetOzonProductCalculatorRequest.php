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
use BaksDev\Ozon\Orders\Type\ProfileType\TypeProfileDbsOzon;
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

    /** Включить DEBUG */
    private bool $debug = false;

    /** Ширина, см */
    public function width(int|float $width): self
    {
        /** Переводим миллиметры в сантиметры */
        $this->width = (int) (ceil($width * 0.1));
        return $this;
    }

    /** Высота, см */
    public function height(int|float $height): self
    {
        /** Переводим миллиметры в сантиметры */
        $this->height = (int) (ceil($height * 0.1));
        return $this;
    }

    /** Длина, см */
    public function length(int|float $length): self
    {
        /** Переводим миллиметры в сантиметры */
        $this->length = (int) (ceil($length * 0.1));
        return $this;
    }

    /** Вес, кг */
    public function weight(int|float $weight): self
    {
        /** Переводим граммы в килограммы */
        $this->weight = (int) ceil($weight * 0.001);
        return $this;
    }

    public function price(Money $price): self
    {
        $this->price = $price;
        return $this;
    }


    public function enableDebug(): self
    {
        $this->debug = true;
        return $this;
    }

    /**
     * Расчет стоимости услуг Озон
     */
    public function calc(): Money|false
    {
        /**
         * null || array
         * array - включит и покажет DEBUG
         */
        $debug = true === $this->debug ? [] : null;

        /** Делаем проверку всех свойств */
        $reflect = new ReflectionClass($this);
        $properties = $reflect->getProperties(ReflectionProperty::IS_PRIVATE);

        foreach($properties as $property)
        {
            $name = $property->getName();

            if($name === 'debug')
            {
                continue;
            }

            if(empty($this->{$name}))
            {
                throw new InvalidArgumentException(sprintf('Invalid Argument %s', $name));
            }
        }


        /**
         * Присваиваем торговую наценку токена для расчета стоимости услуг
         */

        is_array($debug) ? $debug['Первоначальная стоимость продукта'] = $this->price->getValue() : $debug = null;

        $startPrice = $this->price->getValue();
        $trade = $this->getPercent();
        $this->price->applyString($trade);

        is_array($debug) ? $debug['После процента токена'] = $this->price->getValue() : $debug = null;


        /** Объём отгрузки, Расчет литров */
        $volume = ($this->width * $this->height * $this->length / 1000);
        $volume = ceil($volume);


        /**
         * Вознаграждение для автомобильных шин
         * - FBO 8%
         * - FBS 10%
         */
        $offset = $this->price->percent(10);
        $this->price->add($offset);
        is_array($debug) ? $debug['Вознаграждение 10%'] = $offset->getValue() : $debug = null;


        /**
         * Эквайринг 1.9%
         */
        $eqv = $this->price->percent(1.9);
        $this->price->add($eqv);
        is_array($debug) ? $debug['Эквайринг 2%'] = $eqv->getValue() : $debug = null;


        /**
         * Обработка отправления селлере - 20 руб
         */
        $this->price->add(new Money(20)); // Обработка отправления - 20 руб
        is_array($debug) ? $debug['Обработка отправления селлере'] = 20 : $debug = null;

        /**
         * Доставка до места выдачи - 25 руб
         */
        $this->price->add(new Money(25)); // Доставка до места выдачи
        is_array($debug) ? $debug['Доставка до места выдачи'] = 25 : $debug = null;


        /**
         * Стоимость логистики (объем, литры)
         *
         * @see https://seller-edu.ozon.ru/commissions-tariffs/commissions-tariffs-ozon/rashody-na-dostavku#fbs
         */


        if(true === $this->getType()->equals(TypeProfileFbsOzon::class))
        {
            /**
             * 1. Обработка отправления в сортировочном центе
             */

            $post = match (true)
            {
                $volume <= 1000 => 1.9,
                $volume > 1000 && $volume <= 4000 => 1.7,
                $volume > 4000 && $volume <= 16000 => 1.5,
                $volume > 16000 => 1.3,
                default => 0 // На случай, если объем отрицательный
            };

            $post *= $volume;

            $this->price->add(new Money($post)); // Обработка отправления за литры
            is_array($debug) ? $debug['Обработка в сортировочном центре'] = $post : $debug = null;

            /**
             * 2. Логистика отправления
             */

            // Логистика FBS
            $logistics = match (true)
            {
                // до 0,4 литра включительно — 43 ₽;
                // отключено после 1 июня
                // $volume <= 0.4 => 43,

                // свыше 0,4 литра до 1 литра включительно — 80 ₽;
                $volume <= 1 => 80,

                // до 190 литров включительно — 18 ₽ за каждый дополнительный литр свыше объёма 1 л;
                $volume <= 190 => ($volume * 18 + 80),

                // свыше 190 литров — 3478 ₽.
                default => 3478
            };

            $this->price->add(new Money($logistics)); // Логистика
            is_array($debug) ? $debug['Логистика'] = '('.$this->width.'x'.$this->height.'x'.$this->length.') '.$logistics : $debug = null;
        }

        if(is_array($debug))
        {
            $debug['Итого затраты'] = ($this->price->getValue() - $startPrice);

            dd($debug);
        }

        return $this->price;

    }
}
