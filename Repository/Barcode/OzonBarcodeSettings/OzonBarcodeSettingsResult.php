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
 *
 */

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Repository\Barcode\OzonBarcodeSettings;

final readonly class OzonBarcodeSettingsResult
{
    public function __construct(
        private ?int $counter,
        private ?bool $name,
        private ?bool $offer,
        private ?bool $variation,
        private ?bool $modification,
        private ?string $property,
        private ?string $custom
    ) {}

    /**
     * Counter
     */
    public function getCounter(): int
    {
        return empty($this->counter) ? 1 : $this->counter;
    }

    /**
     * Name
     */
    public function isName(): bool
    {
        return $this->name === true;
    }

    /**
     * Offer
     */
    public function isOffer(): bool
    {
        return $this->offer === true;
    }

    /**
     * Variation
     */
    public function isVariation(): bool
    {
        return $this->variation === true;
    }

    /**
     * Modification
     */
    public function isModification(): bool
    {
        return $this->modification === true;
    }

    /**
     * Property
     */
    public function getProperty(): array
    {
        return $this->property ? json_decode($this->property, false, 512, JSON_THROW_ON_ERROR) : [];
    }

    /**
     * Custom
     */
    public function getCustom(): array
    {
        return $this->custom ? json_decode($this->custom, false, 512, JSON_THROW_ON_ERROR) : [];
    }
}