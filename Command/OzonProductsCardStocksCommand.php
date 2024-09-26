<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BaksDev\Ozon\Products\Command;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Products\Messenger\Card\OzonProductsCardMessage;
use BaksDev\Ozon\Products\Messenger\Stocks\OzonProductsStocksMessage;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardInterface;
use BaksDev\Ozon\Repository\AllProfileToken\AllProfileOzonTokenInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Получаем карточки товаров и добавляем отсутствующие
 */
#[AsCommand(
    name: 'baks:ozon-products:post:stocks',
    description: 'Обновляет остатки на Ozon'
)]
class OzonProductsCardStocksCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly AllProfileOzonTokenInterface $allProfileOzonToken,
        private readonly AllProductsIdentifierInterface $allProductsIdentifier,
        private readonly ProductsOzonCardInterface $productsOzonCard,
        private readonly MessageDispatchInterface $messageDispatch
    ) {
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

        $questions[] = 'Все';

        foreach($profiles as $quest)
        {
            $questions[] = $quest->getAttr();
        }

        $question = new ChoiceQuestion(
            'Профиль пользователя',
            $questions,
            0
        );

        $profileName = $helper->ask($input, $output, $question);

        if($profileName === 'Все')
        {
            /** @var UserProfileUid $profile */
            foreach($profiles as $profile)
            {
                $this->update($profile, $input->getOption('article'));
            }
        }
        else
        {
            $UserProfileUid = null;

            foreach($profiles as $profile)
            {
                if($profile->getAttr() === $profileName)
                {
                    /* Присваиваем профиль пользователя */
                    $UserProfileUid = $profile;
                    break;
                }
            }

            if($UserProfileUid)
            {
                $this->update($UserProfileUid, $input->getOption('article'));
            }

        }

        $this->io->success('Наличие успешно обновлено');

        return Command::SUCCESS;
    }

    public function update(UserProfileUid $profile, ?string $article = null): void
    {
        $this->io->note(sprintf('Обновляем профиль %s', $profile->getAttr()));

        /* Получаем все имеющиеся карточки в системе */
        $products = $this->allProductsIdentifier->findAll();

        if($products === false)
        {
            $this->io->warning('Карточек для обновления не найдено');
            return;
        }


        foreach($products as $product)
        {
            $card = $this->productsOzonCard
                ->forProduct($product['product_id'])
                ->forOfferConst($product['offer_const'])
                ->forVariationConst($product['variation_const'])
                ->forModificationConst($product['modification_const'])
                ->find();

            /**
             * Если передан артикул - применяем фильтр по вхождению
             */
            if(!empty($article))
            {
                /** Пропускаем обновление, если соответствие не найдено */
                if($card === false || stripos($card['article'], $article) === false)
                {
                    continue;
                }
            }

            $OzonProductsCardMessage = new OzonProductsCardMessage(
                $product['product_id'],
                $product['offer_const'],
                $product['variation_const'],
                $product['modification_const'],
                $profile,
            );

            $OzonProductsStocksMessage = new OzonProductsStocksMessage($OzonProductsCardMessage);

            /** Консольную комманду выполняем синхронно */
            $this->messageDispatch->dispatch($OzonProductsStocksMessage);

            $this->io->text(sprintf('Обновили артикул %s', $card['article']));
        }
    }
}
