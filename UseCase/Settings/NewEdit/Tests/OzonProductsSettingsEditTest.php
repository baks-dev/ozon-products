<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\UseCase\Settings\NewEdit\Tests;

use BaksDev\Ozon\Products\Entity\Settings\OzonProductsSettings;
use BaksDev\Ozon\Products\Repository\Settings\OzonProductsSettingsCurrentEvent\OzonProductsSettingsCurrentEventInterface;
use BaksDev\Ozon\Products\UseCase\Settings\NewEdit\OzonProductsSettingsDTO;
use BaksDev\Ozon\Products\UseCase\Settings\NewEdit\OzonProductsSettingsHandler;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 *
 * @group ozon-products-usecase
 *
 * @depends BaksDev\Ozon\Products\UseCase\Settings\NewEdit\Tests\OzonProductsSettingsNewTest::class
 */
#[When(env: 'test')]
class OzonProductsSettingsEditTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var OzonProductsSettingsCurrentEventInterface $OzonProductsSettingsCurrentEvent */
        $OzonProductsSettingsCurrentEvent = self::getContainer()->get(OzonProductsSettingsCurrentEventInterface::class);
        $OzonProductsSettingsEvent = $OzonProductsSettingsCurrentEvent->findByProfile(CategoryProductUid::TEST);
        self::assertNotNull($OzonProductsSettingsEvent);

        /** @see OzonProductsSettingsDTO */
        $OzonProductsSettingsDTO = new OzonProductsSettingsDTO();
        $OzonProductsSettingsEvent->getDto($OzonProductsSettingsDTO);

        self::assertEquals(17027949, $OzonProductsSettingsDTO->getOzon());

        $OzonProductsSettingsDTO->setOzon(22222222);

        self::assertEquals(22222222, $OzonProductsSettingsDTO->getOzon());

        /** @var OzonProductsSettingsHandler $OzonProductsSettingsHandler */
        $OzonProductsSettingsHandler = self::getContainer()->get(OzonProductsSettingsHandler::class);
        $handle = $OzonProductsSettingsHandler->handle($OzonProductsSettingsDTO);

        self::assertTrue(($handle instanceof OzonProductsSettings), $handle.': Ошибка OzonProductsSettings');

    }

}
