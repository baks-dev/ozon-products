<?php

namespace BaksDev\Ozon\Products\Type\Settings\Property;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;

final class OzonProductPropertyType extends Type
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return (string) new OzonProductProperty($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?OzonProductProperty
    {
        return !empty($value) ? new OzonProductProperty($value) : null;
    }

    public function getName(): string
    {
        return OzonProductProperty::TYPE;
    }


    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }
}
