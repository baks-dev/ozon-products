<?php

namespace BaksDev\Ozon\Products\Type\Settings\Attribute;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;

final class OzonProductAttributeType extends Type
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return (string) new OzonProductAttribute($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?OzonProductAttribute
    {
        return !empty($value) ? new OzonProductAttribute($value) : null;
    }

    public function getName(): string
    {
        return OzonProductAttribute::TYPE;
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
