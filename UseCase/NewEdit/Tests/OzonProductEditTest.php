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

namespace BaksDev\Ozon\Products\UseCase\NewEdit\Tests;

use BaksDev\Core\BaksDevCoreBundle;
use BaksDev\Ozon\Products\Entity\Custom\OzonProductCustom;
use BaksDev\Ozon\Products\UseCase\NewEdit\Images\OzonProductCustomImagesDTO;
use BaksDev\Ozon\Products\UseCase\NewEdit\OzonCustomProductDTO;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

#[Group('ozon-products')]
#[When(env: 'test')]
final class OzonProductEditTest extends KernelTestCase
{
    #[DependsOnClass(OzonProductNewTest::class)]
    public function testEdit(): void
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

        $fileSystem->copy(
            BaksDevCoreBundle::PATH.implode(
                DIRECTORY_SEPARATOR,
                ['Resources', 'assets', 'img', 'empty.webp']
            ),
            $testUploadDir.DIRECTORY_SEPARATOR.'photo1.webp'
        );

        $filePhoto = new File($testUploadDir.DIRECTORY_SEPARATOR.'photo.webp', false);

        $em = $container->get(EntityManagerInterface::class);

        /** @var OzonProductCustom $product */
        $product = $em
            ->getRepository(OzonProductCustom::class)
            ->find(ProductInvariableUid::TEST);

        self::assertNotNull($product);

        $editDTO = new OzonCustomProductDTO();
        $imageDTO = new OzonProductCustomImagesDTO();
        $imageDTO->setFile($filePhoto);
    }
}