<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\UseCase\Settings\NewEdit\Attributes;

use BaksDev\Ozon\Products\Entity\Settings\Attribute\OzonProductsSettingsAttributeInterface;
use BaksDev\Ozon\Products\Type\Settings\Attribute\OzonProductAttribute;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see OzonAttribute */
final class OzonProductsSettingsAttributeDTO implements OzonProductsSettingsAttributeInterface
{
    /**
     * Тип характеристики
     */
    private ?OzonProductAttribute $type = null;


    /**
     * Связь на свойство продукта в категории
     */
    #[Assert\Uuid]
    private ?CategoryProductSectionFieldUid $field = null;


    public function getType(): OzonProductAttribute
    {
        return $this->type;
    }

    public function setType(OzonProductAttribute $type): void
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

}
