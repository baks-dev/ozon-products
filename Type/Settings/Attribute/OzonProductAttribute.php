<?php

namespace BaksDev\Ozon\Products\Type\Settings\Attribute;

use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;

final class OzonProductAttribute
{
    public const TYPE = 'ozon_product_attribute';

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

        throw new \InvalidArgumentException(sprintf('Undefined Ozon Products Attribute %s', $property));
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
            static function ($className) {
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
