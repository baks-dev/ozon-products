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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Ozon\Products\BaksDevOzonProductsBundle;
use BaksDev\Ozon\Products\Type\Barcode\Event\OzonBarcodeEventUid;
use BaksDev\Ozon\Products\Type\Barcode\Event\OzonBarcodeEventUidType;
use BaksDev\Ozon\Products\Type\Custom\Image\OzonProductImageType;
use BaksDev\Ozon\Products\Type\Custom\Image\OzonProductImageUid;
use BaksDev\Ozon\Products\Type\Settings\Attribute\OzonProductAttribute;
use BaksDev\Ozon\Products\Type\Settings\Attribute\OzonProductAttributeType;
use BaksDev\Ozon\Products\Type\Settings\Event\OzonProductsSettingsEventType;
use BaksDev\Ozon\Products\Type\Settings\Event\OzonProductsSettingsEventUid;
use BaksDev\Ozon\Products\Type\Settings\Property\OzonProductProperty;
use BaksDev\Ozon\Products\Type\Settings\Property\OzonProductPropertyType;
use Symfony\Config\DoctrineConfig;

return static function(DoctrineConfig $doctrine, ContainerConfigurator $configurator): void {

    $doctrine->dbal()->type(OzonProductsSettingsEventUid::TYPE)->class(OzonProductsSettingsEventType::class);
    $doctrine->dbal()->type(OzonProductAttribute::TYPE)->class(OzonProductAttributeType::class);
    $doctrine->dbal()->type(OzonProductProperty::TYPE)->class(OzonProductPropertyType::class);
    $doctrine->dbal()->type(OzonProductImageUid::TYPE)->class(OzonProductImageType::class);
    $doctrine->dbal()->type(OzonBarcodeEventUid::TYPE)->class(OzonBarcodeEventUidType::class);


    $emDefault = $doctrine->orm()->entityManager('default')->autoMapping(true);

    $emDefault
        ->mapping('ozon-products')
        ->type('attribute')
        ->dir(BaksDevOzonProductsBundle::PATH.'Entity')
        ->isBundle(false)
        ->prefix(BaksDevOzonProductsBundle::NAMESPACE.'\\Entity')
        ->alias('ozon-products');
};
