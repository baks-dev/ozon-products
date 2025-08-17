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

namespace BaksDev\Ozon\Products\Repository\OzonProductImageLocal\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Ozon\Products\Repository\OzonProductImageLocal\OzonProductImageLocalInterface;
use BaksDev\Ozon\Products\Repository\OzonProductImageLocal\OzonProductImageLocalRepository;
use BaksDev\Ozon\Products\Repository\OzonProductImageLocal\OzonProductImageLocalResult;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;


/**
 * @group ozon-products
 */
#[Group('ozon-products')]
#[When(env: 'test')]
class OzonProductImageLocalRepositoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var OzonProductImageLocalInterface $OzonProductImageLocalRepository */
        $OzonProductImageLocalRepository = self::getContainer()->get(OzonProductImageLocalInterface::class);

        $result = $OzonProductImageLocalRepository->findAll();

        foreach($result as $OzonProductImageLocalResult)
        {
            // Вызываем все геттеры
            $reflectionClass = new ReflectionClass(OzonProductImageLocalResult::class);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach($methods as $method)
            {
                // Методы без аргументов
                if($method->getNumberOfParameters() === 0)
                {
                    // Вызываем метод
                    $data = $method->invoke($OzonProductImageLocalResult);
                    // dump($data);
                }
            }
        }

        self::assertTrue(true);
    }

}