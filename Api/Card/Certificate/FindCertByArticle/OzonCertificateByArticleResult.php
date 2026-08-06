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

namespace BaksDev\Ozon\Products\Api\Card\Certificate\FindCertByArticle;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

/** @see OzonCertificateByArticle */
final readonly class OzonCertificateByArticleResult
{
    public function __construct(
        private int $certificate_id, // ": 1120593,
        private string $certificate_name, // ": "Test comment DS. Post-script prod2stg",
        private string $certificate_number, // ": "ЕАЭС RU С-CN.НВ46.В.00413/21",
        private string $type_code, // ": "certificate_of_conformity",
        private string $status_code, // ": "approved",
        private string $accordance_type_code, // ": "technical_regulations_cu",
        private string $rejection_reason_code, // ": "",
        private string $issue_date, // ": "2021-08-12T03:00:00Z",
        private string $expire_date, // ": "2024-08-11T03:00:00Z",
        private int $products_count, // ": 0,
        private string $verification_comment, // ": ""
    ) {}

    public function getCertificateId(): int
    {
        return $this->certificate_id;
    }

    public function getCertificateName(): string
    {
        return $this->certificate_name;
    }

    public function getCertificateNumber(): string
    {
        return $this->certificate_number;
    }

    public function getTypeCode(): string
    {
        return $this->type_code;
    }

    public function getStatusCode(): string
    {
        return $this->status_code;
    }

    public function getAccordanceTypeCode(): string
    {
        return $this->accordance_type_code;
    }

    public function getRejectionReasonCode(): string
    {
        return $this->rejection_reason_code;
    }

    public function getIssueDate(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->issue_date);
    }

    public function getExpireDate(): ?DateTimeImmutable
    {
        return empty($this->expire_date) ? null : new DateTimeImmutable($this->expire_date);
    }

    public function getProductsCount(): int
    {
        return $this->products_count;
    }

    public function getVerificationComment(): string
    {
        return $this->verification_comment;
    }
}