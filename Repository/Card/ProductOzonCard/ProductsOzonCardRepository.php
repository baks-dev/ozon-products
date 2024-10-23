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

namespace BaksDev\Ozon\Products\Repository\Card\ProductOzonCard;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\DeliveryTransport\Entity\ProductParameter\DeliveryPackageProductParameter;
use BaksDev\Ozon\Products\Entity\Settings\Attribute\OzonProductsSettingsAttribute;
use BaksDev\Ozon\Products\Entity\Settings\Event\OzonProductsSettingsEvent;
use BaksDev\Ozon\Products\Entity\Settings\OzonProductsSettings;
use BaksDev\Ozon\Products\Entity\Settings\Property\OzonProductsSettingsProperty;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Seo\CategoryProductSeo;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Description\ProductDescription;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\Price\ProductOfferPrice;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Image\ProductModificationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Price\ProductModificationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Price\ProductVariationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Seo\ProductSeo;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use InvalidArgumentException;

final class ProductsOzonCardRepository implements ProductsOzonCardInterface
{
    private ProductUid|false $product = false;

    private ProductOfferConst|false $offerConst = false;

    private ProductVariationConst|false $variationConst = false;

    private ProductModificationConst|false $modificationConst = false;


    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}


    public function forProduct(Product|ProductUid|string $product): self
    {
        if(is_string($product))
        {
            $product = new ProductUid($product);
        }

        if($product instanceof Product)
        {
            $product = $product->getId();
        }

        $this->product = $product;

        return $this;
    }

    public function forOfferConst(ProductOfferConst|string|null|false $offerConst): self
    {
        if(is_null($offerConst) || $offerConst === false)
        {
            $this->offerConst = false;
            return $this;
        }

        if(is_string($offerConst))
        {
            $offerConst = new ProductOfferConst($offerConst);
        }

        $this->offerConst = $offerConst;

        return $this;
    }

    public function forVariationConst(ProductVariationConst|string|null|false $variationConst): self
    {
        if(is_null($variationConst) || $variationConst === false)
        {
            $this->variationConst = false;
            return $this;
        }

        if(is_string($variationConst))
        {
            $variationConst = new ProductVariationConst($variationConst);
        }

        $this->variationConst = $variationConst;

        return $this;
    }

    public function forModificationConst(ProductModificationConst|string|null|false $modificationConst): self
    {
        if(is_null($modificationConst) || $modificationConst === false)
        {
            $this->modificationConst = false;
            return $this;
        }

        if(is_string($modificationConst))
        {
            $modificationConst = new ProductModificationConst($modificationConst);
        }

        $this->modificationConst = $modificationConst;

        return $this;
    }

    /**
     * Метод получает карточку товара
     */
    public function find(): array|false
    {
        if($this->product === false)
        {
            throw new InvalidArgumentException('Invalid Argument product');
        }

        $dbal = $this
            ->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id')
            ->from(Product::class, 'product')
            ->where('product.id = :product')
            ->setParameter('product', $this->product, ProductUid::TYPE);

        $dbal
            ->addSelect('product_info.article AS product_article')
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id'
            );

        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product.event AND product_trans.local = :local'
            );

        $dbal
            ->addSelect('product_desc.preview AS product_preview')
            ->leftJoin(
                'product',
                ProductDescription::class,
                'product_desc',
                'product_desc.event = product.event AND product_desc.device = :device '
            )->setParameter('device', 'pc');


        /* Категория */
        $dbal->leftJoin(
            'product',
            ProductCategory::class,
            'product_category',
            'product_category.event = product.event AND product_category.root = true'
        );

        $dbal
            ->addSelect('category.id AS category_id')
            ->join(
                'product_category',
                CategoryProduct::class,
                'category',
                'category.id = product_category.category'
            );

        $dbal
            ->addSelect('product_seo.keywords AS product_keywords')
            ->leftJoin(
                'product',
                ProductSeo::class,
                'product_seo',
                'product_seo.event = product.event AND product_seo.local = :local'
            );

        /* Базовая Цена товара */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event'
        );

        /* Фото товара */
        $dbal->leftJoin(
            'product',
            ProductPhoto::class,
            'product_photo',
            'product_photo.event = product.event'
        );


        /* Offer Value, Postfix */
        $dbal->addSelect(
            '
                NULL AS product_offer_postfix,
                NULL AS product_offer_value
            '
        );

        /* Variation Value, Postfix */
        $dbal->addSelect(
            '
                NULL AS product_variation_postfix,
                NULL AS product_variation_value
            '
        );

        /* Variation Value, Postfix */
        $dbal->addSelect(
            '
                NULL AS product_modification_postfix,
                NULL AS product_modification_value
            '
        );

        if($this->offerConst)
        {
            $this->dbalOffer($dbal);

            if($this->variationConst)
            {
                if($this->modificationConst)
                {
                    /**
                     * Modification
                     */
                    /* Массив с селектом стоимости продукта */
                    $selectPrice[] = 'NULLIF(product_modification_price.price, 0)';

                    /* Массив с селектом со старой стоимости продукта */
                    $selectOldPrice[] = 'NULLIF(product_modification_price.old, 0)';

                    /* Массив с селектом валюты продукта */
                    $selectCurrency[] = 'CASE WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 THEN product_modification_price.currency END';

                    /* Массив с селектома артикула товара */
                    $selectArticle[] = 'WHEN product_modification.article IS NOT NULL THEN product_modification.article';

                    /* Массив с селектом наличия продукта */
                    $selectQuantity[] = 'WHEN product_modification_quantity.quantity > 0 AND product_modification_quantity.quantity > product_modification_quantity.reserve 
                                           THEN product_modification_quantity.quantity - ABS(product_modification_quantity.reserve)';

                    /* Массив с селектом фото товара */
                    $selectPhoto[] = "
                        WHEN product_modification_image.ext IS NOT NULL
                        THEN JSONB_BUILD_OBJECT
                            (
                                'product_photo_root', product_modification_image.root,
                                'product_photo_name', CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_image.name),
                                'product_photo_ext', product_modification_image.ext,
                                'product_photo_cdn', product_modification_image.cdn
                            )";


                    /* Массив с селектома атриббутов */
                    $selectAttribute[] = 'WHEN product_modification_params.value IS NOT NULL THEN product_modification_params.value';


                    /** Характеристики упаковки товара  */
                    $dbal
                        ->addSelect('product_package.length') // Длина упаковки в см.
                        ->addSelect('product_package.width')  // Ширина упаковки в см.
                        ->addSelect('product_package.height') // Высота упаковки в см.
                        ->addSelect('product_package.weight') // Вес товара в кг с учетом упаковки (брутто).
                    ;
                }

                /**
                 * Variation
                 */
                /* Массив с селектом стоимости продукта */
                $selectPrice[] = 'NULLIF(product_variation_price.price, 0)';

                /* Массив с селектом со старой стоимости продукта */
                $selectOldPrice[] = 'NULLIF(product_variation_price.old, 0)';

                /* Массив с селектом валюты продукта */
                $selectCurrency[] = 'CASE WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 THEN product_variation_price.currency END';

                /* Массив с селектома артикула товара */
                $selectArticle[] = 'WHEN product_variation.article IS NOT NULL THEN product_variation.article';

                /* Массив с селектом наличия продукта */
                $selectQuantity[] = 'WHEN product_variation_quantity.quantity > 0 AND product_variation_quantity.quantity > product_variation_quantity.reserve 
                                       THEN product_variation_quantity.quantity - ABS(product_variation_quantity.reserve)';

                /* Массив с селектом фото товара */
                $selectPhoto[] = "
                    WHEN product_variation_image.ext IS NOT NULL
                    THEN JSONB_BUILD_OBJECT
                        (
                            'product_photo_root', product_variation_image.root,
                            'product_photo_name', CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name),
                            'product_photo_ext', product_variation_image.ext,
                            'product_photo_cdn', product_variation_image.cdn
                        )";

                /* Массив с селектома атриббутов */
                $selectAttribute[] = 'WHEN product_variation_params.value IS NOT NULL THEN product_variation_params.value';
            }

            /**
             * Product Offer
             */
            /* Массив с селектом стоимости продукта */
            $selectPrice[] = 'NULLIF(product_offer_price.price, 0)';

            /* Массив с селектом со старой стоимости продукта */
            $selectOldPrice[] = 'NULLIF(product_offer_price.old, 0)';

            /* Массив с селектом валюты продукта */
            $selectCurrency[] = 'CASE WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 THEN product_offer_price.currency END';

            /* Массив с селектома артикула товара */
            $selectArticle[] = 'WHEN product_offer.article IS NOT NULL THEN product_offer.article';

            /* Массив с селектом наличия продукта */
            $selectQuantity[] = 'WHEN product_offer_quantity.quantity > 0 AND product_offer_quantity.quantity > product_offer_quantity.reserve 
                                   THEN product_offer_quantity.quantity - ABS(product_offer_quantity.reserve)';

            /* Массив с селектом фото товара */
            $selectPhoto[] = "
                WHEN product_offer_image.ext IS NOT NULL
                THEN JSONB_BUILD_OBJECT
                    (
                        'product_photo_root', product_offer_image.root,
                        'product_photo_name', CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_image.name),
                        'product_photo_ext', product_offer_image.ext,
                        'product_photo_cdn', product_offer_image.cdn
                    )";

            /* Массив с селектома атриббутов */
            $selectAttribute[] = 'WHEN product_offer_params.value IS NOT NULL THEN product_offer_params.value';
        }

        /**
         * Default
         */
        /* Массив с селектом стоимости продукта */
        $selectPrice[] = 'NULLIF(product_price.price, 0)';

        /* Старая стоимость продукта */
        $selectOldPrice[] = 'NULLIF(product_price.old, 0)';

        /* Массив с селектом валюты продукта */
        $selectCurrency[] = 'CASE WHEN product_price.price IS NOT NULL AND product_price.price > 0 THEN product_price.currency END';

        /* Массив с селектома артикула товара */
        $selectArticle[] = 'WHEN product_info.article IS NOT NULL THEN product_info.article';

        /* Массив с селектом наличия продукта */
        $selectQuantity[] = 'WHEN product_price.quantity > 0 AND product_price.quantity > product_price.reserve 
                               THEN product_price.quantity - ABS(product_price.reserve)';

        /* Массив с селектом фото товара */
        $selectPhoto[] = "
            WHEN product_photo.ext IS NOT NULL
                THEN JSONB_BUILD_OBJECT
                    (
                        'product_photo_root', product_photo.root,
                        'product_photo_name', CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name),
                        'product_photo_ext', product_photo.ext,
                        'product_photo_cdn', product_photo.cdn
                    )";

        /* Массив с селектома атриббутов */
        $selectAttribute[] = 'WHEN product_property_params.value IS NOT NULL THEN product_property_params.value';


        /* Стоимость продукта */
        $dbal->addSelect('COALESCE('.implode(',', $selectPrice).', 0) AS product_price');

        /* Старая стоимость продукта */
        $dbal->addSelect('COALESCE('.implode(',', $selectOldPrice).', 0) AS product_old_price');

        /* Валюта продукта */
        $dbal->addSelect('COALESCE('.implode(',', $selectCurrency).') AS product_currency');

        /* Артикул товара */
        $dbal->addSelect('CASE '.implode(' ', $selectArticle).' ELSE NULL END AS article');

        /* Фото товара */
        $dbal->addSelect(
            'JSON_AGG ( DISTINCT CASE '.implode(' ', $selectPhoto).' END ) AS product_images'
        );

        /* Наличие продукта */
        $dbal->addSelect('CASE '.implode(' ', $selectQuantity).' END AS product_quantity');

        /* Фото товара */
        $dbal->addSelect(
            'JSON_AGG ( DISTINCT CASE '.implode(' ', $selectPhoto).' END ) AS product_images'
        );

        /* Получение аттрибутов */
        $dbal->addSelect(
            "JSON_AGG ( 
                DISTINCT JSONB_BUILD_OBJECT (
                    'id', settings_params.type, 
                    'value', CASE ".implode(' ', $selectAttribute)." ELSE NULL END
                )
            ) 
            AS product_attributes"
        );


        //        /* Modification Value, Postfix */
        //        $dbal
        //            ->addSelect('product_modification.postfix AS product_modification_postfix,
        //                                   product_modification.value AS product_modification_value')
        //            ->leftJoin(
        //                'product',
        //                ProductModification::class,
        //                'product_modification',
        //                'product_offer_price.offer = product_offer.id'
        //            );
        //
        //


        //        /** Характеристики упаковки товара  */
        //        $dbal
        //            ->addSelect('product_package.length') // Длина упаковки в см.
        //            ->addSelect('product_package.width')  // Ширина упаковки в см.
        //            ->addSelect('product_package.height') // Высота упаковки в см.
        //            ->addSelect('product_package.weight') // Вес товара в кг с учетом упаковки (брутто).
        //            ->leftJoin(
        //                'product_variation',
        //                DeliveryPackageProductParameter::class,
        //                'product_package',
        //                '
        //                    product_package.product = product.id AND
        //                    product_package.offer = product_offer.const AND
        //                    product_package.variation = product_variation.const AND
        //                    product_package.modification = product_modification.const
        //                '
        //            );


        /** Категория, согласно настройкам соотношений */
        $dbal
            ->join(
                'product_category',
                OzonProductsSettings::class,
                'settings',
                'settings.id = product_category.category'
            );


        $dbal
            ->addSelect('settings_event.ozon AS ozon_category')
            ->addSelect('settings_event.type AS ozon_type')
            ->leftJoin(
                'settings',
                OzonProductsSettingsEvent::class,
                'settings_event',
                'settings_event.id = settings.event'
            );


        /** Свойства по умолчанию */
        $dbal
            ->leftJoin(
                'settings',
                OzonProductsSettingsProperty::class,
                'settings_property',
                'settings_property.event = settings.event'
            );

        // Получаем значение из свойств товара
        $dbal
            ->leftJoin(
                'settings_property',
                ProductProperty::class,
                'product_property',
                'product_property.event = product.event AND product_property.field = settings_property.field'
            );

        $dbal->addSelect(
            "JSON_AGG
			( DISTINCT

					JSONB_BUILD_OBJECT
					(
						'type', settings_property.type,
			
						'value', CASE
						   WHEN product_property.value IS NOT NULL THEN product_property.value
						   WHEN settings_property.def IS NOT NULL THEN settings_property.def
						   ELSE NULL
						END
					)
			)
			AS product_properties"
        );


        /** Аттрибуты*/
        $dbal
            ->leftJoin(
                'settings',
                OzonProductsSettingsAttribute::class,
                'settings_params',
                'settings_params.event = settings.event'
            );


        // Получаем значение из свойств товара
        $dbal
            ->leftJoin(
                'settings_params',
                ProductProperty::class,
                'product_property_params',
                '
                    product_property_params.event = product.event AND 
                    product_property_params.field = settings_params.field
                '
            );


        $dbal->allGroupByExclude();

        return $dbal
            //->enableCache('ozon-products', 5)
            ->fetchAssociative();
    }


    private function dbalOffer(DBALQueryBuilder $dbal): void
    {

        if($this->offerConst)
        {
            /** Торговое предложение */
            $dbal
                ->addSelect('
                product_offer.postfix AS product_offer_postfix,
                product_offer.value AS product_offer_value,
                product_offer.const AS product_offer_const
            ')
                ->join(
                    'product',
                    ProductOffer::class,
                    'product_offer',
                    'product_offer.event = product.event AND product_offer.const = :offer_const'
                )
                ->setParameter(
                    'offer_const',
                    $this->offerConst,
                    ProductOfferConst::TYPE
                );

            /* Цена торгового предложения */
            $dbal->leftJoin(
                'product_offer',
                ProductOfferPrice::class,
                'product_offer_price',
                'product_offer_price.offer = product_offer.id'
            );


            /* Фот торговых предложений */
            $dbal->leftJoin(
                'product_offer',
                ProductOfferImage::class,
                'product_offer_image',
                '
			    product_offer_image.offer = product_offer.id	
		    '
            );

            /* Наличие и резерв торгового предложения */
            $dbal->leftJoin(
                'product_offer',
                ProductOfferQuantity::class,
                'product_offer_quantity',
                'product_offer_quantity.offer = product_offer.id'
            );

            // Получаем значение из модификации множественного варианта
            $dbal
                ->leftJoin(
                    'settings_params',
                    ProductOffer::class,
                    'product_offer_params',
                    '
                    product_offer_params.id = product_offer.id AND  
                    product_offer_params.category_offer = settings_params.field
                '
                );


            if($this->variationConst)
            {

                $this->dbalVariation($dbal);
            }
        }

    }


    private function dbalVariation(DBALQueryBuilder $dbal): void
    {
        /** Множественный вариант торгового предложения */
        $dbal
            ->addSelect(
                '
                product_variation.postfix AS product_variation_postfix,
                product_variation.value AS product_variation_value,
                product_variation.const AS product_variation_const
            '
            )
            ->join(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id AND product_variation.const = :variation_const'
            )
            ->setParameter(
                'variation_const',
                $this->variationConst,
                ProductVariationConst::TYPE
            );


        /* Цена множественного варианта */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id'
        );

        /* Фото вариантов */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationImage::class,
            'product_variation_image',
            '
			product_variation_image.variation = product_variation.id
			'
        );

        /* Наличие и резерв множественного варианта */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationQuantity::class,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_variation.id'
        );

        $dbal
            ->leftJoin(
                'product_variation',
                ProductVariation::class,
                'product_variation_params',
                '
                    product_variation_params.id = product_variation.id AND
                    product_variation_params.category_variation = settings_params.field
               '
            );


        if($this->modificationConst)
        {
            $this->dbalModification($dbal);
        }

    }

    private function dbalModification(DBALQueryBuilder $dbal): void
    {
        /** Модификатор множественного варианта торгового предложения  */
        $dbal
            ->addSelect(
                '
                product_modification.postfix AS product_modification_postfix,
                product_modification.value AS product_modification_value,
                product_modification.const AS product_modification_const
            '
            )
            ->join(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id AND product_modification.const = :modification_const'
            )
            ->setParameter(
                'modification_const',
                $this->modificationConst,
                ProductModificationConst::TYPE
            );


        /* Цена модификации множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id'
        );

        /* Фото модификаций */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationImage::class,
            'product_modification_image',
            'product_modification_image.modification = product_modification.id'
        );

        /* Наличие и резерв модификации множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationQuantity::class,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_modification.id'
        );

        $dbal->leftJoin(
            'product_modification',
            ProductModification::class,
            'product_modification_params',
            '
                    product_modification_params.id = product_modification.id AND
                    product_modification_params.category_modification = settings_params.field
                '
        );

        $dbal->leftJoin(
            'product_variation',
            DeliveryPackageProductParameter::class,
            'product_package',
            '
                    product_package.product = product.id AND
                    product_package.offer = product_offer.const AND
                    product_package.variation = product_variation.const AND
                    product_package.modification = product_modification.const
                '
        );
    }
}
