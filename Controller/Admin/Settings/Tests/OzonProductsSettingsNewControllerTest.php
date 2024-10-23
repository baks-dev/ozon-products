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

namespace BaksDev\Ozon\Products\Controller\Admin\Settings\Tests;

use BaksDev\Ozon\Products\Mapper\Category\OzonProductsCategoryCollection;
use BaksDev\Ozon\Products\Mapper\Type\OzonProductsTypeCollection;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @group ozon-products
 *
 * @group ozon-products-controller
 * @group ozon-products-usecase
 *
 * @depends BaksDev\Ozon\Products\Controller\Admin\Settings\Tests\OzonProductsSettingsIndexControllerTest::class
 */
#[When(env: 'test')]
final class OzonProductsSettingsNewControllerTest extends WebTestCase
{
    private static ?string $url = null;

    private const ROLE = 'ROLE_OZON_PRODUCTS_NEW';


    public static function setUpBeforeClass(): void
    {
        // Бросаем событие консольной комманды
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $event = new ConsoleCommandEvent(new Command(), new StringInput(''), new NullOutput());
        $dispatcher->dispatch($event, 'console.command');

        $container = self::getContainer();

        /** @var OzonProductsCategoryCollection $OzonProductsCategoryCollection */
        $OzonProductsCategoryCollection = $container->get(OzonProductsCategoryCollection::class);
        $ozon = current($OzonProductsCategoryCollection->cases())->getid();

        /** @var OzonProductsTypeCollection $OzonProductsTypeCollection */
        $OzonProductsTypeCollection = $container->get(OzonProductsTypeCollection::class);
        $type = current($OzonProductsTypeCollection->cases())->getId();

        self::$url = sprintf('/admin/ozon/product/setting/new/%s/%d/%d', CategoryProductUid::TEST, $ozon, $type);
    }


    /** Доступ по роли  */
    public function testRoleSuccessful(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $usr = TestUserAccount::getModer(self::ROLE);

            $client->loginUser($usr, 'user');

            $client->request('GET', self::$url);

            self::assertResponseIsSuccessful();
        }

        self::assertTrue(true);
    }


    /** Доступ по роли ROLE_ADMIN */
    public function testRoleAdminSuccessful(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $usr = TestUserAccount::getAdmin();

            $client->loginUser($usr, 'user');
            $client->request('GET', self::$url);

            self::assertResponseIsSuccessful();
        }

        self::assertTrue(true);
    }

    /** Доступ по роли ROLE_USER */
    public function testRoleUserFiled(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $usr = TestUserAccount::getUsr();
            $client->loginUser($usr, 'user');
            $client->request('GET', self::$url);

            self::assertResponseStatusCodeSame(403);
        }

        self::assertTrue(true);
    }

    /** Доступ по без роли */
    public function testGuestFiled(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $client->request('GET', self::$url);

            // Full authentication is required to access this resource
            self::assertResponseStatusCodeSame(401);
        }

        self::assertTrue(true);
    }
}
