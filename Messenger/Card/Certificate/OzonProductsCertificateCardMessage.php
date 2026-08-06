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

use BaksDev\Ozon\Type\Id\OzonTokenUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see OzonProductsCertificateCardMessage */
final class OzonProductsCertificateCardMessage
{

    private string|int $id;

    private string $token;

    /**
     * @param int|string $id
     * @param string $token
     */
    public function __construct(
        int|string $id,
        OzonTokenUid $token,
    )
    {
        $this->id = $id;
        $this->token = (string) $token;
    }


    /**
     * Id
     */
    public function getId(): int|string
    {
        return $this->id;
    }

    /**
     * Profile
     */
    public function getToken(): OzonTokenUid
    {
        return new OzonTokenUid($this->token);
    }

}