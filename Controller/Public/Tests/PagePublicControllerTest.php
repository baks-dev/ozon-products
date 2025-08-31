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

namespace BaksDev\Ozon\Products\Controller\Public\Tests;

use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Users\User\Tests\TestUserAccount;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('ozon-products')]
#[When(env: 'test')]
final class PagePublicControllerTest extends WebTestCase
{
    private const string ROLE = 'ROLE_OZON';

    /** Доступ по роли */
    public function testRoleSuccessful(): void
    {
        self::ensureKernelShutdown();

        $client = self::createClient();

        $URL = sprintf(
            '/ozon/product/triangle_effexsport_th202/%s/%s/%s/QSQfJdrt',
            ProductOfferUid::TEST,
            ProductVariationUid::TEST,
            ProductModificationUid::TEST,
        );

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);
            $usr = TestUserAccount::getModer(self::ROLE);

            $client->loginUser($usr, 'user');
            $client->request('GET', $URL);

            self::assertResponseRedirects();
        }

        self::assertTrue(true);
    }

    /** Доступ по роли ROLE_ADMIN */
    public function testRoleAdminSuccessful(): void
    {
        self::ensureKernelShutdown();

        $client = self::createClient();

        $URL = sprintf(
            '/ozon/product/triangle_effexsport_th202/%s/%s/%s/ABEPtbJ',
            ProductOfferUid::TEST,
            ProductVariationUid::TEST,
            ProductModificationUid::TEST,
        );

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);
            $usr = TestUserAccount::getAdmin();

            $client->loginUser($usr, 'user');
            $client->request('GET', $URL);

            self::assertResponseRedirects();
            //self::assertResponseIsSuccessful();
        }

        self::assertTrue(true);
    }

    /** Доступ по роли ROLE_USER */
    public function testRoleUserFiled(): void
    {
        self::ensureKernelShutdown();

        $client = static::createClient();

        $URL = sprintf(
            '/ozon/product/triangle_effexsport_th202/%s/%s/%s/KqwDztzpYS',
            ProductOfferUid::TEST,
            ProductVariationUid::TEST,
            ProductModificationUid::TEST,
        );

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $usr = TestUserAccount::getUsr();
            $client->loginUser($usr, 'user');
            $client->request('GET', $URL);

            self::assertResponseRedirects();
            //self::assertResponseIsSuccessful();
        }

        self::assertTrue(true);
    }

    /** Доступ без роли */
    public function testGuestFiled(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();

        $URL = sprintf(
            '/ozon/product/triangle_effexsport_th202/%s/%s/%s/DRTxHhgXF',
            ProductOfferUid::TEST,
            ProductVariationUid::TEST,
            ProductModificationUid::TEST,
        );

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);
            $client->request('GET', $URL);

            self::assertResponseRedirects();
            //self::assertResponseIsSuccessful();
        }

        self::assertTrue(true);
    }
}
