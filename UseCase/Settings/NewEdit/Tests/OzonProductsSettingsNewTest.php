<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\UseCase\Settings\NewEdit\Tests;

use BaksDev\Ozon\Products\Entity\Settings\Event\OzonProductsSettingsEvent;
use BaksDev\Ozon\Products\Entity\Settings\OzonProductsSettings;
use BaksDev\Ozon\Products\Repository\Settings\OzonProductsSettingsCurrentEvent\OzonProductsSettingsCurrentEventInterface;
use BaksDev\Ozon\Products\UseCase\Settings\NewEdit\OzonProductsSettingsDTO;
use BaksDev\Ozon\Products\UseCase\Settings\NewEdit\OzonProductsSettingsHandler;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\UseCase\Admin\NewEdit\Tests\CategoryProductNewTest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 * @group ozon-products-controller
 * @group ozon-products-usecase
 */
#[When(env: 'test')]
class OzonProductsSettingsNewTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {

        // 0188a99c-ab4b-7c1a-be5d-14f2b990284d

        $CategoryProductNewTest = new CategoryProductNewTest();
        $CategoryProductNewTest::setUpBeforeClass();
        $CategoryProductNewTest->testUseCase();


        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $main = $em->getRepository(OzonProductsSettings::class)
            ->findOneBy(['id' => CategoryProductUid::TEST]);

        if($main)
        {
            $em->remove($main);
        }


        $event = $em->getRepository(OzonProductsSettingsEvent::class)
            ->findBy(['settings' => CategoryProductUid::TEST]);

        foreach($event as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();
        $em->clear();
    }


    public function testUseCase(): void
    {

        /** @see OzonProductsSettingsDTO */
        $OzonProductsSettingsDTO = new OzonProductsSettingsDTO();

        $OzonProductsSettingsDTO->setOzon(17027949);
        $OzonProductsSettingsDTO->setType(94765);
        $OzonProductsSettingsDTO->setSettings(new CategoryProductUid(CategoryProductUid::TEST));

        self::assertEquals(17027949, $OzonProductsSettingsDTO->getOzon());
        self::assertEquals(94765, $OzonProductsSettingsDTO->getType());


        /** @var OzonProductsSettingsHandler $OzonProductsSettingsHandler */
        $OzonProductsSettingsHandler = self::getContainer()->get(OzonProductsSettingsHandler::class);
        $handle = $OzonProductsSettingsHandler->handle($OzonProductsSettingsDTO);

        self::assertTrue(($handle instanceof OzonProductsSettings), $handle.': Ошибка OzonProductsSettings');

    }

}
