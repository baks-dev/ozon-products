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

namespace BaksDev\Ozon\Products\UseCase\Barcode\NewEdit\Tests;

use BaksDev\Ozon\Products\Entity\Barcode\Event\OzonBarcodeEvent;
use BaksDev\Ozon\Products\Entity\Barcode\OzonBarcode;
use BaksDev\Ozon\Products\UseCase\Barcode\NewEdit\Custom\OzonBarcodeCustomDTO;
use BaksDev\Ozon\Products\UseCase\Barcode\NewEdit\OzonBarcodeDTO;
use BaksDev\Ozon\Products\UseCase\Barcode\NewEdit\OzonBarcodeHandler;
use BaksDev\Ozon\Products\UseCase\Barcode\NewEdit\Property\OzonBarcodePropertyDTO;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('ozon-products')]
#[When(env: 'test')]
final class NewHandleTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $OzonBarcode = $em->getRepository(OzonBarcode::class)
            ->findOneBy(['id' => CategoryProductUid::TEST, 'profile' => UserProfileUid::TEST]);

        if($OzonBarcode)
        {
            $em->remove($OzonBarcode);
        }

        $OzonBarcodeEventCollection = $em->getRepository(OzonBarcodeEvent::class)
            ->findBy(['main' => CategoryProductUid::TEST]);

        foreach($OzonBarcodeEventCollection as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();
    }

    public function testUseCase(): void
    {
        $OzonBarcodeDTO = new OzonBarcodeDTO();

        // setCategory
        $CategoryProductUid = new CategoryProductUid();
        $OzonBarcodeDTO->setMain($CategoryProductUid);
        self::assertSame($CategoryProductUid, $OzonBarcodeDTO->getMain());

        $OzonBarcodeDTO->getName()->setValue(true);
        self::assertTrue($OzonBarcodeDTO->getName()->getValue());

        // setOffer
        $OzonBarcodeDTO->getOffer()->setValue(true);
        self::assertTrue($OzonBarcodeDTO->getOffer()->getValue());

        // setVariation
        $OzonBarcodeDTO->getVariation()->setValue(true);
        self::assertTrue($OzonBarcodeDTO->getVariation()->getValue());

        // setModification
        $OzonBarcodeDTO->getModification()->setValue(true);
        self::assertTrue($OzonBarcodeDTO->getModification()->getValue());

        // setCounter
        $OzonBarcodeDTO->getCounter()->setValue(3);
        self::assertEquals(3, $OzonBarcodeDTO->getCounter()->getValue());


        $OzonBarcodePropertyDTO = new OzonBarcodePropertyDTO();

        // setOffer
        $CategoryProductSectionFieldUid = new  CategoryProductSectionFieldUid();
        $OzonBarcodePropertyDTO->setOffer($CategoryProductSectionFieldUid);
        self::assertSame($CategoryProductSectionFieldUid, $OzonBarcodePropertyDTO->getOffer());

        // setSort
        $OzonBarcodePropertyDTO->setSort(100);
        self::assertEquals(100, $OzonBarcodePropertyDTO->getSort());

        // setName
        $OzonBarcodePropertyDTO->setName('Property');
        self::assertEquals('Property', $OzonBarcodePropertyDTO->getName());

        // addProperty
        $OzonBarcodeDTO->addProperty($OzonBarcodePropertyDTO);
        self::assertTrue($OzonBarcodeDTO->getProperty()->contains($OzonBarcodePropertyDTO));


        $OzonBarcodeCustomDTO = new OzonBarcodeCustomDTO();

        // setSort
        $OzonBarcodeCustomDTO->setSort(100);
        self::assertEquals(100, $OzonBarcodeCustomDTO->getSort());

        // setName
        $OzonBarcodeCustomDTO->setName('Custom');
        self::assertEquals('Custom', $OzonBarcodeCustomDTO->getName());

        // setValue
        $OzonBarcodeCustomDTO->setValue('Value');
        self::assertEquals('Value', $OzonBarcodeCustomDTO->getValue());

        // addCustom
        $OzonBarcodeDTO->addCustom($OzonBarcodeCustomDTO);
        self::assertTrue($OzonBarcodeDTO->getCustom()->contains($OzonBarcodeCustomDTO));


        self::bootKernel();

        /** @var OzonBarcodeHandler $OzonBarcodeHandler */
        $UserProfileUid = new UserProfileUid();
        $OzonBarcodeHandler = self::getContainer()->get(OzonBarcodeHandler::class);
        $handle = $OzonBarcodeHandler->handle($OzonBarcodeDTO, $UserProfileUid);

        self::assertTrue(($handle instanceof OzonBarcode), $handle.': Ошибка OzonBarcode');

    }

    public function testComplete(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $OzonBarcode = $em->getRepository(OzonBarcode::class)
            ->findOneBy(['id' => CategoryProductUid::TEST, 'profile' => UserProfileUid::TEST]);
        self::assertNotNull($OzonBarcode);

        self::assertTrue(true);
    }
}