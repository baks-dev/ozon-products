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

namespace BaksDev\Ozon\Products\Command;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Products\Messenger\Card\OzonProductsCardMessage;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Ozon\Repository\AllProfileToken\AllProfileOzonTokenInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Получаем карточки товаров и добавляем отсутствующие
 */
#[AsCommand(
    name: 'baks:ozon-products:update:cards',
    description: 'Обновляет все карточки на Ozon'
)]
class UpdateOzonProductsCardCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly AllProfileOzonTokenInterface $allProfileOzonToken,
        private readonly AllProductsIdentifierInterface $AllProductsIdentifier,
        private readonly ProductsOzonCardInterface $ProductsOzonCard,
        private readonly MessageDispatchInterface $messageDispatch
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('article', 'a', InputOption::VALUE_OPTIONAL, 'Фильтр по артикулу ((--article=... || -a ...))');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        /** Получаем активные токены авторизации профилей Ozon */
        $profiles = $this->allProfileOzonToken
            ->onlyActiveToken()
            ->findAll();

        $profiles = iterator_to_array($profiles);

        $helper = $this->getHelper('question');


        /**
         * Интерактивная форма списка профилей
         */

        $questions[] = 'Все';

        foreach($profiles as $quest)
        {
            $questions[] = $quest->getAttr();
        }

        $questions['+'] = 'Выполнить все асинхронно';
        $questions['-'] = 'Выйти';

        $question = new ChoiceQuestion(
            'Профиль пользователя (Ctrl+C чтобы выйти)',
            $questions,
            '0'
        );

        $key = $helper->ask($input, $output, $question);

        /**
         *  Выходим без выполненного запроса
         */

        if($key === '-' || $key === 'Выйти')
        {
            return Command::SUCCESS;
        }


        /**
         * Выполняем все с возможностью асинхронно в очереди
         */

        if($key === '+' || $key === '0' || $key === 'Все')
        {
            /** @var UserProfileUid $profile */
            foreach($profiles as $profile)
            {
                $this->update($profile, $input->getOption('article'), $key === '+');
            }

            $this->io->success('Заказы успешно обновлены');
            return Command::SUCCESS;
        }


        /**
         * Выполняем определенный профиль
         */

        $UserProfileUid = null;

        foreach($profiles as $profile)
        {
            if($profile->getAttr() === $questions[$key])
            {
                /* Присваиваем профиль пользователя */
                $UserProfileUid = $profile;
                break;
            }
        }

        if($UserProfileUid)
        {
            $this->update($UserProfileUid, $input->getOption('article'));

            $this->io->success('Карточки успешно обновлены');
            return Command::SUCCESS;
        }


        $this->io->error('Профиль пользователя не найден');
        return Command::INVALID;

    }

    public function update(UserProfileUid $profile, ?string $article = null, bool $async = false): void
    {
        $this->io->note(sprintf('Обновили профиль %s', $profile->getAttr()));

        /** Получаем все имеющиеся карточки профиля */
        $result = $this->AllProductsIdentifier->findAll();

        foreach($result as $product)
        {
            $card = $this->ProductsOzonCard
                ->forProduct($product['product_id'])
                ->forOfferConst($product['offer_const'])
                ->forVariationConst($product['variation_const'])
                ->forModificationConst($product['modification_const'])
                ->find();

            if($card === false)
            {
                $this->io->writeln('<fg=red>Карточка товара либо настройки соотношений не найдено</>');
                continue;
            }


            /** Пропускаем обновление, если соответствие не найдено */
            if(!empty($article) && stripos($card['article'], $article) === false)
            {
                continue;
            }

            if(empty($card['product_price']))
            {
                $this->io->writeln(sprintf('<fg=yellow>Карточка товара с артикулом %s без цены</>', $card['article']));
                continue;
            }

            $OzonProductsCardMessage = new OzonProductsCardMessage(
                $profile,
                new ProductUid($product['product_id']),
                $product['offer_const'] ? new ProductOfferConst($product['offer_const']) : false,
                $product['variation_const'] ? new ProductVariationConst($product['variation_const']) : false,
                $product['modification_const'] ? new ProductModificationConst($product['modification_const']) : false,

            );

            /** Консольную комманду выполняем синхронно */
            $this->messageDispatch->dispatch(
                message: $OzonProductsCardMessage,
                transport: $async === true ? (string) $profile : null
            );

            $this->io->text(sprintf('Обновили карточку %s', $card['article']));

        }
    }
}
