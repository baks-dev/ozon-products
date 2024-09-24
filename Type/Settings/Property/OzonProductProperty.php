<?php

namespace BaksDev\Ozon\Products\Type\Settings\Property;

use BaksDev\Ozon\Products\Mapper\Property\OzonProductsPropertyInterface;

final class OzonProductProperty
{
    public const TYPE = 'ozon_product_property';

    private ?OzonProductsPropertyInterface $property = null;

    public function __construct(OzonProductsPropertyInterface|self|string $property)
    {
        if(is_string($property) && class_exists($property))
        {
            $instance = new $property();

            if($instance instanceof OzonProductsPropertyInterface)
            {
                $this->property = $instance;
                return;
            }
        }

        if($property instanceof OzonProductsPropertyInterface)
        {
            $this->property = $property;
            return;
        }

        if($property instanceof self)
        {
            $this->property = $property->getOzonProductProperty();
            return;
        }

        /** @var OzonProductsPropertyInterface $declare */
        foreach(self::getDeclared() as $declare)
        {
            if($declare::equals($property))
            {
                $this->property = new $declare();
                return;
            }
        }

        throw new \InvalidArgumentException(sprintf('Undefined Ozon Products Property %s', $property));
    }


    public function __toString(): string
    {
        return $this->property ? $this->property->getvalue() : '';
    }

    public function getOzonProductProperty(): ?OzonProductsPropertyInterface
    {
        return $this->property;
    }

    public function getOzonProductPropertyValue(): string
    {
        return $this->property->getValue();
    }


    public static function cases(): array
    {
        $case = [];

        foreach(self::getDeclared() as $property)
        {
            /** @var OzonProductsPropertyInterface $property */
            $class = new $property();
            $case[$class::priority()] = new self($class);
        }

        return $case;
    }

    public static function getDeclared(): array
    {
        return array_filter(
            get_declared_classes(),
            static function ($className) {
                return in_array(OzonProductsPropertyInterface::class, class_implements($className), true);
            }
        );
    }

    public function equals(mixed $property): bool
    {
        $property = new self($property);

        return $this->getOzonProductPropertyValue() === $property->getOzonProductPropertyValue();
    }
}
