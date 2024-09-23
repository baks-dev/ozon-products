<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Ozon\Products\UseCase\Settings\Delete\Tests;

use BaksDev\Ozon\Products\Entity\Settings\Event\OzonProductsSettingsEvent;
use BaksDev\Ozon\Products\Entity\Settings\OzonProductsSettings;
use BaksDev\Ozon\Products\Repository\Settings\OzonProductsSettingsCurrentEvent\OzonProductsSettingsCurrentEventInterface;
use BaksDev\Ozon\Products\UseCase\Settings\Delete\DeleteOzonProductsSettingsDTO;
use BaksDev\Ozon\Products\UseCase\Settings\Delete\DeleteOzonProductsSettingsHandler;
use BaksDev\Ozon\Products\UseCase\Settings\NewEdit\OzonProductsSettingsDTO;
use BaksDev\Ozon\Products\UseCase\Settings\NewEdit\Tests\OzonProductsSettingsNewTest;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\UseCase\Admin\NewEdit\Tests\CategoryProductNewTest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 *
 * @group ozon-products-usecase
 *
 * @depends BaksDev\Ozon\Products\UseCase\Settings\NewEdit\Tests\OzonProductsSettingsNewTest::class
 * @depends BaksDev\Ozon\Products\UseCase\Settings\NewEdit\Tests\OzonProductsSettingsEditTest::class
 */
#[When(env: 'test')]
class OzonProductsSettingsDeleteTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var OzonProductsSettingsCurrentEventInterface $OzonProductsSettingsCurrentEvent */
        $OzonProductsSettingsCurrentEvent = self::getContainer()->get(OzonProductsSettingsCurrentEventInterface::class);
        $OzonProductsSettingsEvent = $OzonProductsSettingsCurrentEvent->findByProfile(CategoryProductUid::TEST);
        self::assertNotNull($OzonProductsSettingsEvent);
        self::assertNotFalse($OzonProductsSettingsEvent);


        /** @see OzonProductsSettingsDTO */
        $OzonProductsSettingsDTO = new OzonProductsSettingsDTO();
        $OzonProductsSettingsEvent->getDto($OzonProductsSettingsDTO);

        self::assertEquals(CategoryProductUid::TEST, $OzonProductsSettingsDTO->getSettings());

        self::assertEquals(22222222, $OzonProductsSettingsDTO->getOzon());

        /** @see DeleteOzonProductsSettingsDTO */
        $OzonProductsSettingsDeleteDTO = new DeleteOzonProductsSettingsDTO();
        $OzonProductsSettingsEvent->getDto($OzonProductsSettingsDeleteDTO);

        /** @var DeleteOzonProductsSettingsHandler $OzonTokenHandler */
        $OzonTokenDeleteHandler = self::getContainer()->get(DeleteOzonProductsSettingsHandler::class);
        $handle = $OzonTokenDeleteHandler->handle($OzonProductsSettingsDeleteDTO);

        self::assertTrue(($handle instanceof OzonProductsSettings), $handle.': Ошибка OzonProducts');

    }

    public static function tearDownAfterClass(): void
    {
        OzonProductsSettingsNewTest::setUpBeforeClass();
        CategoryProductNewTest::setUpBeforeClass();
    }
}
