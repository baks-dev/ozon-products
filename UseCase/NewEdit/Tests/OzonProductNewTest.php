<?php
/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Products\UseCase\NewEdit\Tests;

use BaksDev\Core\BaksDevCoreBundle;
use BaksDev\Ozon\Products\Entity\Custom\Images\OzonProductCustomImage;
use BaksDev\Ozon\Products\Entity\Custom\OzonProductCustom;
use BaksDev\Ozon\Products\Type\Custom\Image\OzonProductImageUid;
use BaksDev\Ozon\Products\UseCase\NewEdit\Images\OzonProductCustomImagesDTO;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use BaksDev\Ozon\Products\UseCase\NewEdit\OzonCustomProductDTO;
use BaksDev\Ozon\Products\UseCase\NewEdit\OzonCustomProductHandler;

/**
 * @group ozon-products
 * @group ozon-products-usecase
 */
#[When(env: 'test')]
final class OzonProductNewTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        // Бросаем событие консольной комманды
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $event = new ConsoleCommandEvent(new Command(), new StringInput(''), new NullOutput());
        $dispatcher->dispatch($event, 'console.command');

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $ozonProduct = $em
            ->getRepository(OzonProductCustom::class)
            ->findOneBy(['invariable' => ProductInvariableUid::TEST]);

        if(!empty($ozonProduct))
        {
            $em->remove($ozonProduct);
        }

        $ozonProductImages = $em
            ->getRepository(OzonProductCustomImage::class)
            ->findBy(['invariable' => ProductInvariableUid::TEST]);

        foreach($ozonProductImages as $ozonProductImage)
        {
            $em->remove($ozonProductImage);
        }

        $em->flush();
        $em->clear();
    }

    public function testNew(): void
    {
        /** @var ContainerBagInterface $containerBag */
        $container = self::getContainer();
        $containerBag = $container->get(ContainerBagInterface::class);
        $fileSystem = $container->get(Filesystem::class);

        /** Создаем путь к тестовой директории */
        $testUploadDir = implode(
            DIRECTORY_SEPARATOR,
            [$containerBag->get('kernel.project_dir'), 'public', 'upload', 'tests']
        );

        /** Проверяем существование директории для тестовых картинок */
        if(false === is_dir($testUploadDir))
        {
            $fileSystem->mkdir($testUploadDir);
        }

        /**
         * Создаем тестовое изображение
         */
        $fileSystem = $container->get(Filesystem::class);

        $fileSystem->copy(
            BaksDevCoreBundle::PATH.implode(
                DIRECTORY_SEPARATOR,
                ['Resources', 'assets', 'img', 'empty.webp']
            ),
            $testUploadDir.DIRECTORY_SEPARATOR.'photo.webp'
        );

        $filePhoto = new File($testUploadDir.DIRECTORY_SEPARATOR.'photo.webp', false);

        /**
         * Тестируем OzonProductImagesDTO
         */
        $image = new OzonProductCustomImagesDTO();

        $image->setFile($filePhoto);
        self::assertEquals($image->getFile(), $filePhoto);

        $image->setExt('webp');
        self::assertTrue($image->getExt() === 'webp');

        $image->setId(OzonProductImageUid::TEST);
        self::assertTrue($image->getId()->equals(OzonProductImageUid::TEST));

        $image->setName('test');
        self::assertTrue($image->getName() === 'test');

        $image->setRoot(true);
        self::assertTrue($image->getRoot() === true);

        $image->setSize(1);
        self::assertTrue($image->getSize() === 1);

        /**
         * Тестируем OzonProductDTO
         */
        $ozonProductDTO = new OzonCustomProductDTO();

        $ozonProductDTO->setInvariable(ProductInvariableUid::TEST);
        self::assertTrue($ozonProductDTO->getInvariable()->equals(ProductInvariableUid::TEST));

        $ozonProductDTO->getImages()->add($image);

        $container = self::getContainer();

        /** @var OzonCustomProductHandler $handler */
        $handler = $container->get(OzonCustomProductHandler::class);
        $newOzonProduct = $handler->handle($ozonProductDTO);
        self::assertTrue($newOzonProduct instanceof OzonProductCustom);
    }
}