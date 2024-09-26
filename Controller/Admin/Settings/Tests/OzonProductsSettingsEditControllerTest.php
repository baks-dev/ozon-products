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

use BaksDev\Ozon\Products\Repository\Settings\OzonProductsSettingsCurrentEvent\OzonProductsSettingsCurrentEventInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 *
 * @group ozon-products-controller
 * @group ozon-products-usecase
 *
 * @depends BaksDev\Ozon\Products\Controller\Admin\Settings\Tests\OzonProductsSettingsNewControllerTest::class
 */

#[When(env: 'test')]
final class OzonProductsSettingsEditControllerTest extends WebTestCase
{
    private static ?string $url = null;

    private const ROLE = 'ROLE_OZON_PRODUCTS_EDIT';


    public static function setUpBeforeClass(): void
    {

        /** @var OzonProductsSettingsCurrentEventInterface $OzonProductsSettingsCurrentEvent */
        $OzonProductsSettingsCurrentEvent = self::getContainer()->get(OzonProductsSettingsCurrentEventInterface::class);
        $OzonProductsSettingsEvent = $OzonProductsSettingsCurrentEvent->findByProfile(CategoryProductUid::TEST);

        self::$url = sprintf('/admin/ozon/product/setting/edit/%s', $OzonProductsSettingsEvent);
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
