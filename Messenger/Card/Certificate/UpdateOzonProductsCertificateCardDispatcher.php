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
use BaksDev\Ozon\Products\Api\Card\Certificate\Create\OzonProductCertificateBindRequest;
use BaksDev\Ozon\Products\Api\Card\Certificate\Create\OzonProductCertificateCreateRequest;
use BaksDev\Ozon\Products\Api\Card\Certificate\FindCertByArticle\FindOzonCertByArticleRequest;
use BaksDev\Ozon\Products\Api\Card\Update\GetOzonCardStatusUpdateRequest;
use BaksDev\Products\Product\Repository\CertificatesByProduct\CertificatesByProductInterface;
use BaksDev\Products\Product\Repository\CertificatesByProduct\CertificatesByProductResult;
use BaksDev\Products\Product\Repository\CurrentProductByArticle\CurrentProductByBarcodeResult;
use BaksDev\Products\Product\Repository\CurrentProductByArticle\ProductConstByArticleInterface;
use BaksDev\Products\Product\Repository\ProductByArticle\ProductEventByArticleInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
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
        #[Target('ozonProductsLogger')] private LoggerInterface $logger,
        private MessageDispatchInterface $messageDispatch,
        private GetOzonCardStatusUpdateRequest $GetOzonCardStatusUpdateRequest,
        private FindOzonCertByArticleRequest $FindOzonCertByArticleRequest,
        private OzonProductCertificateCreateRequest $OzonProductCertificateCreateRequest,
        private OzonProductCertificateBindRequest $OzonProductCertificateBindRequest,
        private ProductConstByArticleInterface $ProductConstByArticleRepository,
        private CertificatesByProductInterface $CertificatesByProductRepository,
        #[Autowire('%kernel.project_dir%')] private string $upload,
        #[Autowire(env: 'CDN_HOST')] private ?string $CDN_HOST = null,
    ) {}

    public function __invoke(OzonProductsCertificateCardMessage $message): void
    {

        // Проверяем информацию о выполненном задании
        $result = $this->GetOzonCardStatusUpdateRequest
            ->forTokenIdentifier($message->getToken())
            ->get($message->getId());

        //        $result = [
        //            'offer_id' => 'TR-PL01-15-185-60-88R',
        //            //'product_id' => 4627729707, // SKU
        //            'product_id' => 4967397384,
        //            'status' => 'imported',
        //        ];


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

        $this->logger->info(
            sprintf('Обновляем сертификаты продукта: %s', $result['offer_id']),
            [$result, self::class.':'.__LINE__],
        );

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
            ->forTokenIdentifier($message->getToken())
            ->setArticle($result['offer_id'])
            ->findAll();

        foreach($certificates as $CertificatesByProductResult)
        {
            /** Если список имеющихся в селлере сертификатов отсутствует - добавляем системный сертификат */
            if(false === $existsCertificates || false === $existsCertificates->valid())
            {
                $this->addSertificate($CertificatesByProductResult, $message, $result);

                continue;
            }

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

                // Пропускаем отправку если файл сертификата отправлен на CDN, но не сервер не указана в настройках .env
                if(empty($this->CDN_HOST) && true === $CertificatesByProductResult->isCdn())
                {
                    continue;
                }

                $this->addSertificate($CertificatesByProductResult, $message, $result);
            }
        }

    }

    public function addSertificate(
        CertificatesByProductResult $CertificatesByProductResult,
        OzonProductsCertificateCardMessage $message,
        array $result
    )
    {
        $path = $CertificatesByProductResult->isCdn() ? 'http://'.$this->CDN_HOST : $this->upload.DIRECTORY_SEPARATOR.'public';
        $path .= $CertificatesByProductResult->getFilePath();

        if(false === is_file($path))
        {
            return;
        }

        $type = match ($CertificatesByProductResult->getType()->getDocumentTypeValue())
        {
            "certconf" => "certificate_of_conformity",
            "decconf" => "declaration",
            "certreg" => "certificate_of_registration",
            "regcert" => "registration_certificate",
            "rejletter" => "refused_letter",
            "vetdoc" => "veterinary_cover_document",
            "passafety" => "safety_data_sheet",
        };


        $match = match ($CertificatesByProductResult->getMatch()->getMatchTypeValue())
        {
            "certtechru" => "technical_regulations_rf",
            "certtechts" => "technical_regulations_cu",
            "certgost" => "gost",
            default => null
        };

        /** Отправляем файл сертификата  */
        $cert = $this->OzonProductCertificateCreateRequest
            ->forTokenIdentifier($message->getToken())
            ->article($result['offer_id'])
            ->number($CertificatesByProductResult->getNumber())
            ->issue($CertificatesByProductResult->getIssue())
            ->expire($CertificatesByProductResult->getExpire())
            ->type($type)
            ->match($match)
            ->upload($path);

        if(empty($cert))
        {
            return;
        }

        /** Если получен идентификатор файла сертификата - прикрепляем продукт к сертификату */

        $this->OzonProductCertificateBindRequest
            ->forTokenIdentifier($message->getToken())
            ->cert($cert)
            ->product($result['product_id'])
            ->update();

    }
}
