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

namespace BaksDev\Ozon\Products\Mapper\Attribute\Collection;

use BaksDev\Ozon\Products\Mapper\Attribute\ItemDataBuilderOzonProductsAttribute;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use BaksDev\Ozon\Products\Mapper\Category\Collection\AccessoriesOzonProductsCategory;
use BaksDev\Ozon\Products\Mapper\Category\Collection\ClothesOzonProductsCategory;
use BaksDev\Ozon\Products\Mapper\Category\Collection\CookwareOzonProductsCategory;
use BaksDev\Ozon\Products\Mapper\Category\Collection\TireOzonProductsCategory;
use BaksDev\Ozon\Products\Mapper\Type\Collection\BaseBallsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\HoodieOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\JeansOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\LongsleeveOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\MugsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\SweatshirtsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\TiresCommercialCarsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\TiresPassengerCarsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\TiresSuvCarsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\TiresTrackCarsOzonProductsType;
use BaksDev\Ozon\Products\Mapper\Type\Collection\TShirtsOzonProductsType;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    public const array CATEGORY = [
        AccessoriesOzonProductsCategory::ID, // 41777465 - "Аксессуары"
        ClothesOzonProductsCategory::ID, // 200000933 - "Одежда"
        CookwareOzonProductsCategory::ID, // 17028741 - "Столовая посуда"
        TireOzonProductsCategory::ID, // 17027949 - "Шины"
    ];

    private const array TYPES = [
        BaseBallsOzonProductsType::ID, // 93040 - Бейсболка
        HoodieOzonProductsType::ID, // 93253 - "Худи"
        JeansOzonProductsType::ID, // 93080 - "Джинсы"
        LongsleeveOzonProductsType::ID, // 93148 - "Лонгслив"
        MugsOzonProductsType::ID, // 92499 - Кружка
        SweatshirtsOzonProductsType::ID, //  93216 - "Свитшот"
        TShirtsOzonProductsType::ID, // 93244 - "Футболка"

        TiresPassengerCarsOzonProductsType::ID, // 94765 - "Шины для легковых автомобилей"
        TiresCommercialCarsOzonProductsType::ID, // 97884 - "Шины для коммерческого транспорта"
        TiresSuvCarsOzonProductsType::ID, // 94762 - "Шины для внедорожника",
        TiresTrackCarsOzonProductsType::ID, // 94763 - "Шины для грузовых автомобилей"
    ];

    public const int ID = 11254;

    public function __construct(
        #[Autowire(env: 'HOST')] private readonly ?string $HOST = null,
        #[Autowire(env: 'CDN_HOST')] private readonly ?string $CDN_HOST = null,
    ) {}

    public static function priority(): int
    {
        return 100;
    }

    public function getId(): int
    {
        return self::ID;
    }

    public function getData(ProductsOzonCardResult $data, ?TranslatorInterface $translator): array|false
    {
        if(true === is_null($data->getOzonCategory()))
        {
            return false;
        }

        $name = '';
        $desc = '';

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

                    $desc .= $value.' ';
                }
            }
        }

        //$name = mb_strtolower($name);
        //$name = mb_ucfirst($name);

        $desc = mb_strtolower($desc);
        $desc = mb_ucfirst($desc);


        $name .= $data->getProductName();
        $desc .= $data->getProductName();

        if($data->getProductVariationValue() && $data->getProductVariationValue() !== 'null')
        {
            $name .= ' '.$data->getProductVariationValue();
            $desc .= ' '.$data->getProductVariationValue();
        }

        if($data->getProductModificationValue() && $data->getProductModificationValue() !== 'null')
        {
            $name .= '/'.$data->getProductModificationValue();
            $desc .= '/'.$data->getProductModificationValue();
        }

        if($data->getProductOfferValue() && $data->getProductOfferValue() !== 'null')
        {
            $name .= ' R'.$data->getProductOfferValue();
            $desc .= ' R'.$data->getProductOfferValue();
        }

        if($data->getProductOfferPostfix())
        {
            $name .= ' '.$data->getProductOfferPostfix();
            $desc .= ' '.$data->getProductOfferPostfix();
        }

        if($data->getProductVariationPostfix())
        {
            $name .= ' '.$data->getProductVariationPostfix();
            $desc .= ' '.$data->getProductVariationPostfix();
        }

        if($data->getProductModificationPostfix())
        {
            $name .= ' '.$data->getProductModificationPostfix();
            $desc .= ' '.$data->getProductModificationPostfix();
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
                        $desc .= ' '.$value;
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


        $name = trim($name);
        $desc = trim($desc);


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
                                        'content' => $desc,
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

    public static function equals(int|string $param): bool
    {
        return self::ID === (int) $param;
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

    public function equalsCategory(int $category): bool
    {
        return in_array($category, self::CATEGORY, true);
    }

    public function equalsType(int $type): bool
    {
        return in_array($type, self::TYPES, true);
    }
}
