<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Products\UseCase\Settings\NewEdit\Tests;

use BaksDev\Ozon\Products\Entity\Settings\Event\OzonProductsSettingsEvent;
use BaksDev\Ozon\Products\Entity\Settings\OzonProductsSettings;
use BaksDev\Ozon\Products\UseCase\Settings\NewEdit\OzonProductsSettingsDTO;
use BaksDev\Ozon\Products\UseCase\Settings\NewEdit\OzonProductsSettingsHandler;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\UseCase\Admin\NewEdit\Tests\CategoryProductNewTest;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('ozon-products')]
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
