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
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('ozon-products')]
#[When(env: 'test')]
final class EditHandleTest extends KernelTestCase
{

    #[DependsOnClass(NewHandleTest::class)]
    public function testUseCase(): void
    {

        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** @var OzonBarcodeEvent $OzonBarcodeEvent */

        $OzonBarcodeEvent =
            $em->createQueryBuilder()
                ->select('event')
                ->from(OzonBarcode::class, 'barcode')
                ->where('barcode.id = :id')
                ->setParameter('id', CategoryProductUid::TEST, CategoryProductUid::TYPE)
                ->leftJoin(OzonBarcodeEvent::class, 'event', 'WITH', 'event.id = barcode.event')
                ->getQuery()
                ->getOneOrNullResult();


        self::assertNotNull($OzonBarcodeEvent);


        $OzonBarcodeDTO = new OzonBarcodeDTO();
        $OzonBarcodeEvent->getDto($OzonBarcodeDTO);

        self::assertTrue($OzonBarcodeDTO->getName()->getValue());
        $OzonBarcodeDTO->getName()->setValue(false);

        self::assertTrue($OzonBarcodeDTO->getOffer()->getValue());
        $OzonBarcodeDTO->getOffer()->setValue(false);

        self::assertTrue($OzonBarcodeDTO->getVariation()->getValue());
        $OzonBarcodeDTO->getVariation()->setValue(false);

        self::assertTrue($OzonBarcodeDTO->getModification()->getValue());
        $OzonBarcodeDTO->getModification()->setValue(false);

        self::assertEquals(3, $OzonBarcodeDTO->getCounter()->getValue());
        $OzonBarcodeDTO->getCounter()->setValue(5);

        /** @var OzonBarcodePropertyDTO $OzonBarcodePropertyDTO */
        $OzonBarcodePropertyDTO = $OzonBarcodeDTO->getProperty()->current();

        self::assertEquals(CategoryProductSectionFieldUid::TEST, (string) $OzonBarcodePropertyDTO->getOffer());
        self::assertEquals(100, $OzonBarcodePropertyDTO->getSort());
        self::assertEquals('Property', $OzonBarcodePropertyDTO->getName());


        /** @var OzonBarcodeCustomDTO $OzonBarcodeCustomDTO */
        $OzonBarcodeCustomDTO = $OzonBarcodeDTO->getCustom()->current();

        self::assertEquals(100, $OzonBarcodeCustomDTO->getSort());
        self::assertEquals('Custom', $OzonBarcodeCustomDTO->getName());
        self::assertEquals('Value', $OzonBarcodeCustomDTO->getValue());


        /** EDIT */


        // Property
        $OzonBarcodePropertyDTO->setOffer(new  CategoryProductSectionFieldUid());
        $OzonBarcodePropertyDTO->setSort(50);
        $OzonBarcodePropertyDTO->setName('Property Edit');

        // Custom
        $OzonBarcodeCustomDTO->setSort(50);
        $OzonBarcodeCustomDTO->setName('Custom Edit');
        $OzonBarcodeCustomDTO->setValue('Value Edit');


        $UserProfileUid = new UserProfileUid();
        /** @var OzonBarcodeHandler $OzonBarcodeHandler */
        $OzonBarcodeHandler = $container->get(OzonBarcodeHandler::class);
        $handle = $OzonBarcodeHandler->handle($OzonBarcodeDTO, $UserProfileUid);

        self::assertTrue(($handle instanceof OzonBarcode), $handle.': Ошибка OzonBarcode');
    }
}