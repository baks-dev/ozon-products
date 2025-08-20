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
 *
 */

declare(strict_types=1);

namespace BaksDev\Ozon\Products\UseCase\Barcode\Delete\Tests;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Ozon\Products\Entity\Barcode\Event\OzonBarcodeEvent;
use BaksDev\Ozon\Products\Entity\Barcode\OzonBarcode;
use BaksDev\Ozon\Products\UseCase\Barcode\Delete\OzonBarcodeDeleteDTO;
use BaksDev\Ozon\Products\UseCase\Barcode\Delete\OzonBarcodeDeleteHandler;
use BaksDev\Ozon\Products\UseCase\Barcode\NewEdit\Custom\OzonBarcodeCustomDTO;
use BaksDev\Ozon\Products\UseCase\Barcode\NewEdit\OzonBarcodeDTO;
use BaksDev\Ozon\Products\UseCase\Barcode\NewEdit\Property\OzonBarcodePropertyDTO;
use BaksDev\Ozon\Products\UseCase\Barcode\NewEdit\Tests\EditHandleTest;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 * @group ozon-products-barcode
 *
 * @depends BaksDev\Ozon\Products\UseCase\Barcode\NewEdit\Tests\EditHandleTest::class
 */
#[Group('ozon-products')]
#[Group('ozon-products-barcode')]
#[When(env: 'test')]
final class OzonBarcodeDeleteTest extends KernelTestCase
{

    #[DependsOnClass(EditHandleTest::class)]
    public function testUseCase(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var ORMQueryBuilder $ORMQueryBuilder */
        $ORMQueryBuilder = $container->get(ORMQueryBuilder::class);
        $qb = $ORMQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(OzonBarcode::class, 'barcode')
            ->where('barcode.id = :category')
            ->setParameter('category', CategoryProductUid::TEST, CategoryProductUid::TYPE);

        $qb
            ->select('event')
            ->leftJoin(OzonBarcodeEvent::class,
                'event',
                'WITH',
                'event.id = barcode.event'
            );


        /** @var OzonBarcodeEvent $OzonBarcodeEvent */
        $OzonBarcodeEvent = $qb->getQuery()->getOneOrNullResult();

        $OzonBarcodeDTO = new OzonBarcodeDTO();
        $OzonBarcodeEvent->getDto($OzonBarcodeDTO);


        self::assertFalse($OzonBarcodeDTO->getName()->getValue());
        self::assertFalse($OzonBarcodeDTO->getOffer()->getValue());
        self::assertFalse($OzonBarcodeDTO->getVariation()->getValue());
        self::assertFalse($OzonBarcodeDTO->getModification()->getValue());
        self::assertEquals(5, $OzonBarcodeDTO->getCounter()->getValue());


        /** @var OzonBarcodePropertyDTO $OzonBarcodePropertyDTO */
        $OzonBarcodePropertyDTO = $OzonBarcodeDTO->getProperty()->current();

        self::assertEquals(CategoryProductSectionFieldUid::TEST, (string) $OzonBarcodePropertyDTO->getOffer());
        self::assertEquals(50, $OzonBarcodePropertyDTO->getSort());
        self::assertEquals('Property Edit', $OzonBarcodePropertyDTO->getName());


        /** @var OzonBarcodeCustomDTO $OzonBarcodeCustomDTO */
        $OzonBarcodeCustomDTO = $OzonBarcodeDTO->getCustom()->current();

        self::assertEquals(50, $OzonBarcodeCustomDTO->getSort());
        self::assertEquals('Custom Edit', $OzonBarcodeCustomDTO->getName());
        self::assertEquals('Value Edit', $OzonBarcodeCustomDTO->getValue());


        /** DELETE */

        $OzonBarcodeDeleteDTO = new OzonBarcodeDeleteDTO();
        $OzonBarcodeEvent->getDto($OzonBarcodeDeleteDTO);

        /** @var OzonBarcodeDeleteHandler $OzonBarcodeDeleteHandler */
        $UserProfileUid = new UserProfileUid();
        $OzonBarcodeDeleteHandler = $container->get(OzonBarcodeDeleteHandler::class);
        $handle = $OzonBarcodeDeleteHandler->handle($OzonBarcodeDeleteDTO, $UserProfileUid);
        self::assertTrue(($handle instanceof OzonBarcode), $handle.': Ошибка OzonBarcode');


        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $OzonBarcode = $em->getRepository(OzonBarcode::class)
            ->findOneBy(['id' => CategoryProductUid::TEST, 'profile' => UserProfileUid::TEST]);
        self::assertNull($OzonBarcode);

    }

    /**
     * @depends testUseCase
     */
    #[Depends('testUseCase')]
    public function testComplete(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        /* OzonBarcode */

        $OzonBarcode = $em->getRepository(OzonBarcode::class)
            ->findOneBy(['id' => CategoryProductUid::TEST, 'profile' => UserProfileUid::TEST]);

        if($OzonBarcode)
        {
            $em->remove($OzonBarcode);
        }

        /* OzonBarcodeEvent */

        $OzonBarcodeEventCollection = $em->getRepository(OzonBarcodeEvent::class)
            ->findBy(['main' => CategoryProductUid::TEST]);

        foreach($OzonBarcodeEventCollection as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();

        self::assertNull($OzonBarcode);
    }
}
