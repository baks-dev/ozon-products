<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Products\Messenger\Card\Certificate\Tests;

use BaksDev\Ozon\Products\Messenger\Card\Certificate\OzonProductsCertificateCardMessage;
use BaksDev\Ozon\Products\Messenger\Card\Certificate\UpdateOzonProductsCertificateCardDispatcher;
use BaksDev\Ozon\Type\Id\OzonTokenUid;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


#[Group('ozon-products')]
#[When(env: 'test')]
class OzonProductsCertificateCardTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        self::assertTrue(true);

        // Бросаем событие консольной комманды
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $event = new ConsoleCommandEvent(new Command(), new StringInput(''), new NullOutput());
        $dispatcher->dispatch($event, 'console.command');

        /** @var UpdateOzonProductsCertificateCardDispatcher $UpdateOzonProductsCertificateCardDispatcher */
        $UpdateOzonProductsCertificateCardDispatcher = self::getContainer()->get(UpdateOzonProductsCertificateCardDispatcher::class);

        /**
         * @param int|string $id
         * @param OzonTokenUid $token
         */
        $OzonProductsCertificateCardMessage = new OzonProductsCertificateCardMessage(
            id: 11111111111111,
            token: new OzonTokenUid('38b05f18-9948-7dad-b2af-8eb92619507d'),
        );

        $UpdateOzonProductsCertificateCardDispatcher($OzonProductsCertificateCardMessage);
    }
}