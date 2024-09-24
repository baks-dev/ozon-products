<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class ItemOzonProductsAttribute
{
    public function __construct(
        #[AutowireIterator('baks.ozon.product.attribute', defaultPriorityMethod: 'priority')] private iterable $attribute
    ) {
    }


    /**
     * Возвращает состояние
     */
    public function getData(array $data): array|false
    {
        $request = null;

        /** @var OzonProductsAttributeInterface $item */
        foreach($this->attribute as $item)
        {
            $value = $item->getData($data);

            if($value === null || $value === false)
            {
                continue;
            }

            $request[] = $value;
        }

        return is_null($request) ? false : $request;
    }
}
