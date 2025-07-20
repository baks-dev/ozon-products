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

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Property\Collection;

use BaksDev\Ozon\Products\Api\Settings\AttributeValuesSearch\OzonAttributeValueSearchRequest;
use BaksDev\Ozon\Products\Mapper\Attribute\ItemOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use BaksDev\Ozon\Products\Mapper\Property\OzonProductsPropertyInterface;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[AutoconfigureTag('baks.ozon.product.property')]
final class AttributeOzonProductsProperty implements OzonProductsPropertyInterface
{
    /**
     * Аттрибуты.
     *
     * Array
     * example: "attributes": [
     *     'complex_id' => 0,
     *     'id' => 85,
     *     'values' => []
     *   ]
     *
     */

    public const string PARAM = 'attributes';

    public function __construct(
        #[AutowireIterator('baks.ozon.product.attribute', defaultPriorityMethod: 'priority')] private ?iterable $attribute = null,
        private readonly ?OzonAttributeValueSearchRequest $attributeValueSearchRequest = null
    ) {}


    public function getValue(): string
    {
        return self::PARAM;
    }

    /**
     * Возвращает состояние
     */
    public function getData(ProductsOzonCardResult $data): array|false
    {
        $request = null;

        /** @var OzonProductsAttributeInterface $item */
        foreach($this->attribute as $item)
        {
            /**
             * Если у аттрибута опредлено значение справочника (DICTIONARY)
             * в классе этого аттрибута необходимо реализовать метод  'attributeValueRequest',
             * в который передать поле attributeValueRequest данного класса
             */

            if(
                null !== $this->attributeValueSearchRequest &&
                method_exists($item, 'attributeValueRequest')
            )
            {
                $item->attributeValueRequest($this->attributeValueSearchRequest);
            }

            $value = $item->getData($data);

            if($value === false)
            {
                continue;
            }

            $request[] = $value;
        }

        return is_null($request) ? false : $request;
    }

    /**
     * Возвращает значение по умолчанию
     */
    public function default(): string|bool
    {
        return false;
    }

    /**
     * Метод указывает, нужно ли добавить свойство для заполнения в форму
     */
    public function isSetting(): bool
    {
        return false;
    }


    public function required(): bool
    {
        return false;
    }

    public static function priority(): int
    {
        return 100;
    }

    /**
     * Проверяет, относится ли значение к данному объекту
     */
    public static function equals(string $param): bool
    {
        return self::PARAM === $param;
    }

    public function choices(): bool
    {
        return false;
    }
}
