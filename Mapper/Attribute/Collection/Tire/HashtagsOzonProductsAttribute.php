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

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection\Tire;

use BaksDev\Ozon\Products\Mapper\Attribute\Collection\TypeOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;

final class HashtagsOzonProductsAttribute implements OzonProductsAttributeInterface
{
    //-id: 23171
    //-complex: 0
    //-name: "#Хештеги"
    //-name: "Хештеги"
    //-description: "Хештеги работают так же, как в соцсетях: покупатели могут нажать на них в карточке или написать в поиске. Укажите тут тренд, стиль или тематику. Например, для одежды #oldmoney #kpop #бохо, для мебели #скандинавский_стиль #минимализм #лофт. Не добавляйте бренды, параметры или название товара.  Хештег должен начинаться со знака # и содержать только буквы и цифры. Если хештег состоит из 2+ слов, соедините их нижним подчеркиванием. Длина — не более 30 символов. Укажите не больше 30 хештегов, разделяйте пробелом"
    //-type: "String"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 0


    /** 17027949 - Шины */
    private const int CATEGORY = 17027949;

    private const int ID = 23171;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(ProductsOzonCardResult $data): array|false
    {
        if(empty($data->getProductAttributes()))
        {
            return false;
        }

        $attribute = array_filter(
            $data->getProductAttributes(),
            static fn($n) => self::ID === (int) $n->id,
        );

        if(false === empty($attribute))
        {
            $value = current($attribute)->value;
        }

        /** Строим хештеги исходя из данных в карточке */
        else
        {

            $hashtags = null;

            $hashtags[] = '#шины';
            $hashtags[] = '#шина';
            $hashtags[] = '#автошина';
            $hashtags[] = '#автомобильные_шины';
            $hashtags[] = '#покрышки';
            $hashtags[] = '#купить_шины';
            $hashtags[] = '#шины_с_доставкой';

            if($data->getProductAttributes())
            {
                /** Добавляем хештеги сезонность и назначение */
                $Season = new SeasonOzonProductsAttribute();
                $Type = new TypeOzonProductsAttribute();

                foreach($data->getProductAttributes() as $productAttribute)
                {
                    /** Добавляем сезонность */
                    if($Season::equals($productAttribute->id))
                    {
                        $value = $Season::getConvertName($productAttribute->value);

                        if(empty($value))
                        {
                            continue;
                        }

                        $hashtags[] = '#'.$value;
                        $hashtags[] = '#'.$value.'_шины';
                        $hashtags[] = '#шины_'.$value;
                    }

                    /** Добавляем назначение */
                    if($Type::equals($productAttribute->id))
                    {
                        $value = $Type::getConvertName($productAttribute->value);

                        if(!empty($value))
                        {
                            $hashtags[] = '#шины_'.str_replace(' ', '_', $value);
                            //$hashtags[] = '#автомобильные_шины_'.str_replace(' ', '_', $value);
                        }
                    }
                }
            }
        }

        $requestData = new ItemDataBuilderOzonProductsAttribute(
            self::ID,
            implode(' ', $hashtags),
        );

        return $requestData->getData();
    }

    public function default(): string|false
    {
        return false;
    }

    public function isSetting(): bool
    {
        return true;
    }

    public function required(): bool
    {
        return false;
    }

    public function choices(): array|false
    {
        return false;
    }

    public static function priority(): int
    {
        return 100;
    }

    public static function equals(int|string $param): bool
    {
        return self::ID === (int) $param;
    }

    public function equalsCategory(int $category): bool
    {
        return self::CATEGORY === $category;
    }
}
