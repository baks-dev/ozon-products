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

namespace BaksDev\Ozon\Products\Api\Card\Certificate\Create;

use BaksDev\Ozon\Api\Ozon;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Autoconfigure(public: true, shared: false)]
final class OzonProductCertificateCreateRequest extends Ozon
{
    private const array ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf'];
    private string $article;
    private string $number;
    private string $type;
    private string $match;
    private DateTimeImmutable $issue;
    private ?DateTimeImmutable $expire;

    public function article(string $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function number(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function match(string $match): self
    {
        $this->match = $match;

        return $this;
    }

    public function issue(DateTimeImmutable $issue): self
    {
        $this->issue = $issue;

        return $this;
    }

    public function expire(DateTimeImmutable $expire): self
    {
        $this->expire = $expire;

        return $this;
    }

    /**
     * Метод загружает файл сертификата в систему Ozon и возвращает его идентификатор.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductCertificateCreate
     *
     * @param string $fileUrl URL файла для скачивания
     *
     * @return string Идентификатор загруженного файла (file_id)
     * @throws FileNotFoundException Если файл не найден
     * @throws TransportExceptionInterface Если произошла ошибка при отправке запроса
     */
    public function upload(string $fileUrl): string
    {
        $fileContent = @file_get_contents($fileUrl);

        if($fileContent === false)
        {
            throw new FileNotFoundException(sprintf('Не удалось скачать файл: %s', $fileUrl));
        }

        $tempFilePath = tempnam(sys_get_temp_dir(), 'ozon_cert_');
        file_put_contents($tempFilePath, $fileContent);

        $fileExtension = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));

        if(!in_array($fileExtension, self::ALLOWED_EXTENSIONS, true))
        {
            unlink($tempFilePath);
            throw new InvalidArgumentException(sprintf('Недопустимое расширение файла: %s. Допустимые расширения: %s', $fileExtension, implode(', ', self::ALLOWED_EXTENSIONS)));
        }

        try
        {
            $filePart = DataPart::fromPath($tempFilePath);

            $formFields = [
                'file' => $filePart,
            ];

            $formData = new FormDataPart($formFields);
            $headers = $formData->getPreparedHeaders()->toArray();

            $response = $this->TokenHttpClient()
                ->request(
                    'POST',
                    '/v1/product/certificate/file',
                    [
                        'headers' => $headers,
                        'files' => [$formData->bodyToString()],
                    ],
                );

            $content = $response->toArray(false);

            if($response->getStatusCode() !== 200)
            {
                $this->logger->critical(
                    sprintf('Ошибкa при загрузке файла сертификата в Ozon: %s', $fileUrl),
                    [$content, self::class.':'.__LINE__],
                );

                throw new RuntimeException(sprintf('Ошибка при загрузке файла сертификата: %s', $response->getContent(false)));
            }

            return $content['file_id'] ?? '';

        }
        catch(ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e)
        {
            $this->logger->critical(
                sprintf('Ошибка при отправке запроса к Ozon API: %s', $fileUrl),
                [$e->getMessage(), self::class.':'.__LINE__],
            );

            throw $e;
        }
        finally
        {
            unlink($tempFilePath);
        }
    }
}