<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Products\Messenger\Settings;

use BaksDev\Ozon\Products\Type\Settings\Event\OzonProductsSettingsEventUid;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;

final class OzonProductsSettingsMessage
{
    public function __construct(
        private readonly CategoryProductUid $id,
        private readonly OzonProductsSettingsEventUid $event,
        private ?OzonProductsSettingsEventUid $last = null
    ) {}


    /**
     * Идентификатор
     */
    public function getId(): CategoryProductUid
    {
        return $this->id;
    }


    /**
     * Идентификатор события
     */
    public function getEvent(): OzonProductsSettingsEventUid
    {
        return $this->event;
    }

    /**
     * Идентификатор предыдущего события
     */
    public function getLast(): ?OzonProductsSettingsEventUid
    {
        return $this->last;
    }

}
