<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Messenger\Settings;

use BaksDev\Ozon\Products\Api\Settings\AttributeValues\OzonAttributeValueRequest;
use BaksDev\Ozon\Products\Api\Settings\Category\OzonCategoryRequest;
use BaksDev\Ozon\Products\Api\Settings\Attribute\OzonAttributeRequest;
use BaksDev\Ozon\Products\Api\Settings\Type\OzonTypeRequest;
use BaksDev\Ozon\Products\Repository\Settings\OzonProductsSettingsCurrentEvent\OzonProductsSettingsCurrentEventInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class OzonProductsSettingsHandler
{
    /** Объявляем сервисы для теста */
    public function __construct(
        public OzonProductsSettingsCurrentEventInterface $event
    ) {}

    public function __invoke(OzonProductsSettingsMessage $message): void {}
}
