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

namespace BaksDev\Ozon\Products\Type\Settings\Attribute;

use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use InvalidArgumentException;

final class OzonProductAttribute
{
    public const string TYPE = 'ozon_product_attribute';

    private ?OzonProductsAttributeInterface $property = null;


    public function __construct(OzonProductsAttributeInterface|self|string $property)
    {
        if(is_string($property) && class_exists($property))
        {
            $instance = new $property();

            if($instance instanceof OzonProductsAttributeInterface)
            {
                $this->property = $instance;
                return;
            }
        }

        if($property instanceof OzonProductsAttributeInterface)
        {
            $this->property = $property;
            return;
        }

        if($property instanceof self)
        {
            $this->property = $property->getOzonProductAttribute();
            return;
        }

        /** @var OzonProductsAttributeInterface $declare */
        foreach(self::getDeclared() as $declare)
        {

            if($declare::equals($property))
            {
                $this->property = new $declare();
                return;
            }
        }

        throw new InvalidArgumentException(sprintf('Undefined Ozon Products Attribute %s', $property));
    }

    public function __toString(): string
    {
        return $this->property ? $this->property->getId() : '';
    }

    public function getOzonProductAttribute(): ?OzonProductsAttributeInterface
    {
        return $this->property;
    }

    public function getOzonProductAttributeId(): int
    {
        return $this->property->getId();
    }


    public static function cases(): array
    {
        $case = [];

        foreach(self::getDeclared() as $property)
        {
            /** @var OzonProductsAttributeInterface $property */
            $class = new $property();
            $case[$class::priority()] = new self($class);
        }

        return $case;
    }

    public static function getDeclared(): array
    {
        return array_filter(
            get_declared_classes(),
            static function($className) {
                return in_array(OzonProductsAttributeInterface::class, class_implements($className), true);
            }
        );
    }

    public function equals(mixed $property): bool
    {
        $property = new self($property);

        return $this->getOzonProductAttributeId() === $property->getOzonProductAttributeId();
    }
}
