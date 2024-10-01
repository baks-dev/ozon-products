<?php

namespace BaksDev\Ozon\Products\Entity\Settings\Modify;

use BaksDev\Core\Type\Modify\ModifyAction;

interface OzonProductsSettingsModifyInterface
{
    public function getAction(): ModifyAction;
}
