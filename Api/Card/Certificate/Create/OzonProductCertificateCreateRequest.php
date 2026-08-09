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
use DateTimeInterface;
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
    public function upload(string $path): int|false
    {
        $formFields = [
            'files' => DataPart::fromPath($path),
            'name' => $this->article,
            'number' => $this->number,
            'type_code' => $this->type,
            'accordance_type_code' => $this->match,
            'issue_date' => $this->issue->format(DateTimeInterface::W3C),
            'expire_date' => $this->expire ? $this->expire->format(DateTimeInterface::W3C) : null,
        ];

        $formData = new FormDataPart($formFields);
        $headers = $formData->getPreparedHeaders()->toArray();

        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                '/v1/product/certificate/create',
                [
                    'headers' => $headers,
                    'body' => $formData->bodyToString(),
                ],
            );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            $this->logger->critical(
                sprintf('ozon-products: Ошибка при загрузке файла сертификата в Ozon: %s', $path),
                [$content, self::class.':'.__LINE__],
            );

            return false;
        }

        return $content['id'] ? (int) $content['id'] : false;


    }
}