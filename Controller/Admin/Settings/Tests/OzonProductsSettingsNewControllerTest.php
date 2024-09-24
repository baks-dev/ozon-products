<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
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
