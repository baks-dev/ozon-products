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

namespace BaksDev\Ozon\Products\Repository\Card\ProductOzonCard;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use JsonException;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ProductsOzonCardResult */
final  class ProductsOzonCardResult
{

    private array|null|false $product_attributes_decode = null;

    private array|null|false $product_properties_decode = null;

    private array|false|null $images = null;

    private int|false $dictionary = false;

    public function __construct(

        private readonly string $profile, //" => "018954cb-0a6e-744a-97f0-128e7f05d76d"
        private readonly string $id, //" => "018954cb-0a6e-744a-97f0-128e7f05d76d"
        private readonly string $event, //" => "018954cb-0a6e-744a-97f0-128e7f05d76d"

        private readonly string $product_article, //" => "TH202"
        private readonly string $article, //" => "TH202-16-195-45-84W"
        private readonly ?string $barcode, //" => "2744652835819"


        private readonly string $product_name, //" => "Triangle EffeXSport XL TH202"
        private readonly ?string $product_preview,
        //" => "<p><strong>Triangle EffeXSport TH202</strong> – это новая разработка Triangle Group, впервые представленная в 2022 году, летние спортивные шины с асимметричным рисунком протектора класса UPH (ultra high performance), развитие шин прошлого поколения – SporteX TH201. Благодаря инновационному подходу к разработке и производству по сравнению с предшественником инженерам удалось сделать большой шаг вперед. Тесты показали увеличение ресурса на 22%, точности управления на 12%, комфорта на 14%, динамичности на 9%, а также эффективности торможения на сухой и мокрой поверхностях на 7% и 5% соответственно и курсовой устойчивости на 6%. Данная модель получила высшую оценку «A» по европейской маркировке.</p><p>Шины представлены типоразмерами от 205/40 R17 до 275/35 R20, индексами нагрузки от 84 (до 500 кг) до 110 (до 1060 кг) и индексами скорости W (до 270 км/ч) и Y (до 300 км/ч).</p>"
        private readonly ?string $category_id, //" => "01876af0-ddfc-70c3-ab25-5f85f55a9907"
        private readonly ?string $product_keywords,
        //" => "TH202, Triangle EffeXSport TH202, недорогие шины, летние спортивные шины, китайские шины"

        private readonly ?string $product_invariable, //" => "01946560-2a5e-7b6e-a430-d44a948b2d0d"

        private readonly ?string $product_offer_const, //" => "018db273-839d-7f69-8b4b-228aac5934f1"
        private readonly ?string $product_offer_value, //" => "16"
        private readonly ?string $product_offer_postfix, //" => null

        private readonly ?string $product_variation_postfix, //" => null
        private readonly ?string $product_variation_value, //" => "195"
        private readonly ?string $product_variation_const, //" => "018db273-839c-72dd-bb36-de5c52445d28"

        private readonly ?string $product_modification_postfix, //" => "84W"
        private readonly ?string $product_modification_value, //" => "45"
        private readonly ?string $product_modification_const, //" => "018db273-839c-72dd-bb36-de5c523881be"

        private readonly ?int $length, //" => 100
        private readonly ?int $width, //" => 100
        private readonly ?int $height, //" => 100
        private readonly ?int $weight, //" => 1000

        private readonly ?int $product_price, //" => 530000
        private readonly ?int $product_old_price, //" => 0
        private readonly ?string $product_currency, //" => "rur"
        private readonly ?string $product_quantity, //" => 0

        private readonly ?int $ozon_category, //" => 17027949
        private readonly ?int $ozon_type, //" => 94765

        private readonly ?string $product_attributes,
        //" => "[{"id": "4389", "value": "cn"}, {"id": "7291", "value": "summer"}, {"id": "7381", "value": "false"}, {"id": "7387", "value": "195"}, {"id": "7388", "value": "45"}, {"id": "7389", "value": "16"}, {"id": "8229", "value": "passenger"}]"
        private readonly ?string $product_properties, //" => null

        /** Изображения */
        private readonly ?string $product_images_custom, //" => null
        private ?string $product_images_modification, //" => null
        private ?string $product_images_variation, //" => null
        private ?string $product_images_offer, //" => null
        private ?string $product_images, //" => "[{"product_img": "/upload/product_photo/1d129d8e73de76c01dd3b97060e78a53", "product_img_cdn": true, "product_img_ext": "webp", "product_img_root": false}, {"product_img": "/upload/product_photo/46ee2f0f6adab11443789192821c4ec3", "product_img_cdn": true, "product_img_ext": "webp", "product_img_root": true}, {"product_img": "/upload/product_photo/78483dad1fba1f1eb5debb6efcac0d3c", "product_img_cdn": true, "product_img_ext": "webp", "product_img_root": false}, {"product_img": "/upload/product_photo/7f1ba417142283b0d1bcea461e59499e", "product_img_cdn": true, "product_img_ext": "webp", "product_img_root": false}, {"product_img": "/upload/product_photo/fceaab3fae070675e40e5d0b54593f62", "product_img_cdn": true, "product_img_ext": "webp", "product_img_root": false}]"


    ) {}

    public function getProfile(): UserProfileUid
    {
        return new UserProfileUid($this->profile);
    }

    public function getProductId(): ProductUid
    {
        return new ProductUid($this->id);
    }

    public function getProductEvent(): ProductEventUid
    {
        return new ProductEventUid($this->event);
    }

    /** Метод возвращает артикул карточки */
    public function getCardArticle(): ?string
    {
        return $this->product_article;
    }

    /** Метод возвращает артикул торгового предложения */
    public function getArticle(): ?string
    {
        return $this->article;
    }


    public function getProductName(): ?string
    {
        return $this->product_name;
    }

    public function getProductPreview(): ?string
    {
        return strip_tags($this->product_preview);
    }

    public function getCategoryId(): ?CategoryProductUid
    {
        return new CategoryProductUid($this->category_id);
    }

    public function getProductKeywords(): ?string
    {
        return $this->product_keywords;
    }

    /** OFFER */

    public function getProductOfferConst(): ProductOfferConst|false
    {
        return empty($this->product_offer_const) ? false : new ProductOfferConst($this->product_offer_const);
    }

    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getProductOfferPostfix(): ?string
    {
        return $this->product_offer_postfix;
    }

    /** VARIATION */

    public function getProductVariationPostfix(): ?string
    {
        return $this->product_variation_postfix;
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getProductVariationConst(): ProductVariationConst|false
    {
        return empty($this->product_variation_const) ? false : new ProductVariationConst($this->product_variation_const);
    }

    /** MODIFICATION */

    public function getProductModificationPostfix(): ?string
    {
        return $this->product_modification_postfix;
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }

    public function getProductModificationConst(): ProductModificationConst|false
    {
        return empty($this->product_modification_const) ? false : new ProductModificationConst($this->product_modification_const);
    }

    /** Параметры упаковки */

    /**
     * Длина
     */
    public function getLength(): int|false
    {
        return empty($this->length) ? false : (int) $this->length;
    }

    /**
     * Ширина
     */
    public function getWidth(): int|false
    {
        return empty($this->width) ? false : (int) $this->width;
    }

    /**
     * Высота
     */
    public function getHeight(): int|false
    {
        return empty($this->height) ? false : (int) $this->height;
    }

    /**
     * Вес
     */
    public function getWeight(): int|false
    {
        return empty($this->weight) ? false : (int) $this->weight * 10;
    }


    public function getProductInvariable(): ProductInvariableUid
    {
        return new ProductInvariableUid($this->product_invariable);
    }

    public function getOzonCategory(): ?int
    {
        return $this->ozon_category;
    }

    public function getOzonType(): ?int
    {
        return $this->ozon_type;
    }

    public function getProductPrice(): ?Money
    {
        return new Money($this->product_price, true);
    }

    public function getProductOldPrice(): Money|false
    {
        return empty($this->product_old_price) ? false : new Money($this->product_price, true);
    }

    public function getProductCurrency(): Currency
    {
        return new Currency($this->product_currency);
    }

    public function getProductQuantity(): int
    {
        if(empty($this->product_quantity))
        {
            return 0;
        }

        if(false === json_validate($this->product_quantity))
        {
            return 0;
        }

        $decode = json_decode($this->product_quantity, false, 512, JSON_THROW_ON_ERROR);

        $quantity = 0;

        foreach($decode as $item)
        {
            $quantity += $item->total;
            $quantity -= $item->reserve;
        }

        return max($quantity, 0);
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    /**
     * @throws JsonException
     */
    public function getProductAttributes(): array|false
    {
        if(is_null($this->product_attributes_decode))
        {
            if(empty($this->product_attributes))
            {
                $this->product_attributes_decode = false;
                return false;
            }

            if(false === json_validate($this->product_attributes))
            {
                $this->product_attributes_decode = false;
                return false;
            }

            $this->product_attributes_decode = json_decode($this->product_attributes, false, 512, JSON_THROW_ON_ERROR);

        }

        return $this->product_attributes_decode;
    }

    public function getProductProperties(): array|false
    {
        if(is_null($this->product_properties_decode))
        {
            if(empty($this->product_properties))
            {
                $this->product_properties_decode = false;
                return false;
            }

            if(false === json_validate($this->product_properties))
            {
                $this->product_properties_decode = false;
                return false;
            }

            $decode = json_decode($this->product_properties, false, 512, JSON_THROW_ON_ERROR);
            $this->product_properties_decode = empty($decode) ? false : $decode;

        }

        return $this->product_properties_decode;
    }


    /**
     * Коллекция изображений
     * return array< int, object{ product_img: string, product_img_cdn: bool, product_img_ext: string,
     * product_img_root: bool}>|false
     *
     * @return object{ product_img: string, product_img_cdn: bool, product_img_ext: string, product_img_root: bool
     *     }[]|false
     */
    public function getProductImages(): array|false
    {
        /** Возвращаем массив сформированный массив */
        if(false === is_null($this->images))
        {
            return $this->images;
        }

        /** Если имеется коллекция кастомных изображений - снимаем ROOT с изображений карточки */

        if(false === empty($this->product_images_custom))
        {
            if(false === empty($this->product_images_modification))
            {
                $this->product_images_modification = str_replace('"product_img_root": true', '"product_img_root": false', $this->product_images_modification);
            }

            if(false === empty($this->product_images_variation))
            {
                $this->product_images_variation = str_replace('"product_img_root": true', '"product_img_root": false', $this->product_images_variation);
            }

            if(false === empty($this->product_images_offer))
            {
                $this->product_images_offer = str_replace('"product_img_root": true', '"product_img_root": false', $this->product_images_offer);
            }

            if(false === empty($this->product_images))
            {
                $this->product_images = str_replace('"product_img_root": true', '"product_img_root": false', $this->product_images);
            }
        }

        $collection = [
            $this->product_images_custom,
            $this->product_images_modification,
            $this->product_images_variation,
            $this->product_images_offer,
            $this->product_images,
        ];

        foreach($collection as $images)
        {
            if(empty($images))
            {
                continue;
            }

            if(false === json_validate($images))
            {
                continue;
            }

            $images = json_decode($images, false, 512, JSON_THROW_ON_ERROR);
            $images = array_filter($images, static fn($img) => empty($img->product_img_ext) === false);

            if(empty($images))
            {
                continue;
            }

            $this->images ? array_push($this->images, ...$images) : $this->images = $images;

        }

        if(empty($this->images))
        {
            $this->images = false;
            return false;
        }

        /* Сортируем коллекцию изображений по root */
        uasort($this->images, function($a, $b) {
            // Сначала сравниваем по product_img_root
            if($a->product_img_root === $b->product_img_root)
            {
                return 0; // сохраняем текущий порядок
            }

            // Если у $a true, а у $b false - $a должен быть раньше
            if($a->product_img_root && !$b->product_img_root)
            {
                return -1;
            }

            // Если у $a false, а у $b true - $b должен быть раньше
            return 1;
        });

        return $this->images;
    }


    //    public function getProductImagesCustom(): ?string
    //    {
    //        return $this->product_images_custom;
    //    }
    //
    //    public function getProductImagesModification(): ?string
    //    {
    //        return $this->product_images_modification;
    //    }
    //
    //    public function getProductImagesVariation(): ?string
    //    {
    //        return $this->product_images_variation;
    //    }
    //
    //    public function getProductImagesOffer(): ?string
    //    {
    //        return $this->product_images_offer;
    //    }
    //
    //    public function getProductImages(): ?string
    //    {
    //        return $this->product_images;
    //    }

    public function setDictionaryValue(false|array $getData): self
    {

        if(empty($getData['values']))
        {
            $this->dictionary = false;
            return $this;
        }

        $values = current($getData['values']);

        if(empty($values['dictionary_value_id']))
        {
            $this->dictionary = false;
            return $this;
        }

        $this->dictionary = $values['dictionary_value_id'];

        return $this;
    }

    public function getTypeDictionary(): false|int
    {
        return $this->dictionary;
    }

}