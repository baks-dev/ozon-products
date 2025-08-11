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
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class RichContainOzonProductsAttribute implements OzonProductsAttributeInterface
{

    //-id: 11254
    //-complex: 0
    //-name: "Rich-контент JSON"
    //-description: "Добавьте расширенное описание товара с фото и видео по шаблону в формате JSON. Подробнее о заполнении этой характеристики можно узнать в статье "Rich-контент" в "Базе знаний"."
    //-type: "String"
    //-collection: false
    //-required: false
    //-count: 0
    //-groupId: 0
    //-groupName: ""
    //-dictionary: 0

    public function __construct(
        #[Autowire(env: 'HOST')] private readonly ?string $HOST = null,
        #[Autowire(env: 'CDN_HOST')] private readonly ?string $CDN_HOST = null,
    ) {}

    /** 17027949 - Шины */
    private const int CATEGORY = 17027949;

    private const int ID = 11254;

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(ProductsOzonCardResult $data): array|false
    {
        if(true === is_null($data->getOzonCategory()))
        {
            return false;
        }

        $name = '';

        if($data->getProductAttributes())
        {
            /** Добавляем к названию сезонность */
            $Season = new SeasonOzonProductsAttribute();

            foreach($data->getProductAttributes() as $productAttribute)
            {
                if($Season::equals($productAttribute->id))
                {
                    $value = $Season::getConvertName($productAttribute->value);

                    if(empty($value))
                    {
                        continue;
                    }

                    $name .= $value.' ';
                }
            }
        }

        $name = mb_strtolower($name);
        $name = mb_ucfirst($name);


        $name .= $data->getProductName();

        if($data->getProductVariationValue())
        {
            $name .= ' '.$data->getProductVariationValue();
        }

        if($data->getProductModificationValue())
        {
            $name .= '/'.$data->getProductModificationValue();
        }

        if($data->getProductOfferValue())
        {
            $name .= ' R'.$data->getProductOfferValue();
        }

        if($data->getProductOfferPostfix())
        {
            $name .= ' '.$data->getProductOfferPostfix();
        }

        if($data->getProductVariationPostfix())
        {
            $name .= ' '.$data->getProductVariationPostfix();
        }

        if($data->getProductModificationPostfix())
        {
            $name .= ' '.$data->getProductModificationPostfix();
        }

        if($data->getProductAttributes())
        {
            /** Добавляем к названию назначение */
            $Type = new TypeOzonProductsAttribute();

            foreach($data->getProductAttributes() as $productAttribute)
            {
                if($Type::equals($productAttribute->id))
                {
                    $value = $Type::getConvertName($productAttribute->value);

                    if(!empty($value))
                    {
                        $name .= ' '.$value;
                    }
                }
            }
        }


        if(empty($data->getProductImages()))
        {
            return false;
        }

        $picture = array_filter($data->getProductImages(), static function($v) {
            return $v->product_img_root === true;
        });

        if(empty($picture))
        {
            return false;
        }

        $picture = current($picture);

        $picture = sprintf(
            'https://%s%s/%s.%s',
            $picture->product_img_cdn ? $this->CDN_HOST : $this->HOST,
            $picture->product_img,
            $picture->product_img_cdn ? 'large' : 'image',
            $picture->product_img_ext,
        );


        $content = [
            'content' => [
                [
                    'widgetName' => 'raShowcase',
                    'type' => 'chess',
                    'blocks' => [
                        [

                            'img' => [
                                'src' => $picture,
                                'srcMobile' => $picture,
                                'alt' => $name,
                                'position' => 'to_the_edge',
                                'positionMobile' => 'to_the_edge',
                                'widthMobile' => 1200,
                                'heightMobile' => 1200,
                            ],

                            'imgLink' => '',

                            'title' => [
                                'items' => [
                                    [
                                        'type' => 'text',
                                        'content' => $name,
                                    ],
                                ],
                                'size' => 'size4',
                                'align' => 'left',
                                'color' => 'color1',
                            ],

                            'text' => [
                                'size' => 'size2',
                                'align' => 'left',
                                'color' => 'color1',
                                'items' => [
                                    [
                                        'type' => 'text',
                                        'content' => 'Этот текст уже готов для описания',
                                    ],
                                ],
                            ],

                            'reverse' => false,
                        ],
                    ],
                ],
            ],
            'version' => 0.3,
        ];


        $requestData = new ItemDataBuilderOzonProductsAttribute(
            self::ID,
            json_encode($content, JSON_THROW_ON_ERROR),
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
