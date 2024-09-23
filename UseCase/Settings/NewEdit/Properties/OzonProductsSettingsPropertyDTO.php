<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\UseCase\Settings\NewEdit\Properties;

use BaksDev\Ozon\Products\Entity\Settings\Property\OzonProductsSettingsPropertyInterface;
use BaksDev\Ozon\Products\Type\Settings\Property\OzonProductProperty;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see OzonAttribute */
final class OzonProductsSettingsPropertyDTO implements OzonProductsSettingsPropertyInterface
{
    /** Идентификатор */
    private ?OzonProductProperty $type = null;

    /** Описание свойства */
    #[Assert\Uuid]
    private ?CategoryProductSectionFieldUid $field = null;

    private ?string $def = null;

    public function getType(): ?OzonProductProperty
    {
        return $this->type;
    }

    public function setType(OzonProductProperty $type): void
    {
        $this->type = $type;
    }

    public function getField(): ?CategoryProductSectionFieldUid
    {
        return $this->field;
    }

    public function setField(?CategoryProductSectionFieldUid $field): void
    {
        $this->field = $field;
    }

    public function getDef(): ?string
    {
        return $this->def;
    }

    public function setDef(?string $default): void
    {
        $this->def = $default;
    }

}
