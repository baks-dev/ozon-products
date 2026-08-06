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

namespace BaksDev\Ozon\Products\Messenger\Card\Certificate;


use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Products\Api\Card\Certificate\FindCertByArticle\FindOzonCertByArticleRequest;
use BaksDev\Ozon\Products\Api\Card\Update\GetOzonCardStatusUpdateRequest;
use BaksDev\Products\Product\Repository\CertificatesByProduct\CertificatesByProductInterface;
use BaksDev\Products\Product\Repository\CertificatesByProduct\CertificatesByProductResult;
use BaksDev\Products\Product\Repository\CurrentProductByArticle\CurrentProductByBarcodeResult;
use BaksDev\Products\Product\Repository\CurrentProductByArticle\ProductConstByArticleInterface;
use BaksDev\Products\Product\Repository\ProductByArticle\ProductEventByArticleInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

/**
 *  Обновляем список сертификатов продукта
 */
#[Autoconfigure(shared: false)]
#[AsMessageHandler(priority: 0)]
final readonly class UpdateOzonProductsCertificateCardDispatcher
{
    public function __construct(
        private MessageDispatchInterface $messageDispatch,
        private GetOzonCardStatusUpdateRequest $cardUpdateResultRequest,
        private FindOzonCertByArticleRequest $FindOzonCertByArticleRequest,
        private ProductConstByArticleInterface $ProductConstByArticleRepository,
        private CertificatesByProductInterface $CertificatesByProductRepository,
        #[Autowire(env: 'CDN_HOST')] ?string $CDN_HOST = null,
    ) {}

    public function __invoke(OzonProductsCertificateCardMessage $message): void
    {
        // Проверяем информацию о выполненном задании

        $result = $this->cardUpdateResultRequest
            ->forTokenIdentifier($message->getToken())
            ->get($message->getId());


        /**
         * false - если задание не найдено
         */
        if(false === $result)
        {
            return;
        }

        /**
         * Товар в очереди на обработку - пробуем позже
         */
        if($result['status'] === 'pending')
        {
            $this->messageDispatch
                ->dispatch(
                    message: $message,
                    stamps: [new MessageDelay('1 minutes')],
                    transport: 'ozon-products',
                );

            return;
        }


        /**
         * Задание обновилось с ошибкой
         */
        if(false === empty($result['errors']))
        {
            return;
        }


        /** Получаем продукт по артикулу */

        $CurrentProductByBarcodeResult = $this->ProductConstByArticleRepository
            ->find($result['offer_id']);

        // Получаем список сертификатов продукта

        $certificates = $this->CertificatesByProductRepository
            ->forProduct($CurrentProductByBarcodeResult->getProduct())
            ->findAll();

        if(empty($certificates) || false === $certificates->valid())
        {
            return;
        }

        /**  Получаем все сертификаты в селлере */
        $existsCertificates = $this->FindOzonCertByArticleRequest
            ->setArticle('PyeXNmU')
            ->findAll();


        foreach($certificates as $CertificatesByProductResult)
        {
            /**
             * Итерируемся по имеющимся сертификатам и проверяем наличие в списке
             */
            foreach($existsCertificates as $existsCertificate)
            {
                /** Пропускаем если сертификат с таким номером уже добавлен на данный артикул */
                if($CertificatesByProductResult->getNumber() === $existsCertificate->getCertificateNumber())
                {
                    continue;
                }

                // Отправляем файл сертификата получив "Идентификатор загруженного сертификата"
                if(empty($this->CDN_HOST) && true === $CertificatesByProductResult->isCdn())
                {
                    continue;
                }

                $path = $CertificatesByProductResult->isCdn() ? 'http://'.$this->CDN_HOST : '';
                $path = $path.$CertificatesByProductResult->getFilePath();

                // Читаем файл в строку
                $fileContent = file_get_contents($path);

                /* Указываем путь и название файла для загрузки CDN */
                $formDataFile = DataPart::fromPath($fileContent, 'invoice.pdf', 'application/pdf');


                // Получаем идентификатор товара в селлере

                // Прикрепляем продукт к сертификату по идентификатору продукта и сертификата
            }
        }

    }
}
