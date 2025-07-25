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

use BaksDev\Ozon\Products\Mapper\Property\OzonProductsPropertyInterface;
use BaksDev\Ozon\Products\Repository\Card\ProductOzonCard\ProductsOzonCardResult;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AutoconfigureTag('baks.ozon.product.property')]
final class ImagesOzonProductsProperty implements OzonProductsPropertyInterface
{
    /**
     * Массив ссылок на изображения товара
     * До 15 штук. Изображения показываются на сайте в таком же порядке, как в массиве.
     * Если не передать значение primary_image, первое изображение в массиве будет главным
     * для товара. Если вы передали значение primary_image, передайте до 14 изображений.
     * Если параметр primary_image пустой, передайте до 15 изображений.
     * Формат: адрес ссылки на изображение в общедоступном облачном хранилище.
     * Формат изображения по ссылке — JPG или PNG.
     *
     * Array
     * example: "images": [ ]
     */

    public const string PARAM = 'images';

    public function __construct(
        #[Autowire(env: 'HOST')]
        private readonly ?string $HOST = null,
        #[Autowire(env: 'CDN_HOST')]
        private readonly ?string $CDN_HOST = null,
    ) {}

    public function getValue(): string
    {
        return self::PARAM;
    }

    /**
     * Возвращает состояние
     */
    public function getData(ProductsOzonCardResult $data): array
    {

        if(empty($data->getProductImages()))
        {
            return [];
        }

        /** @var object{ product_img: string, product_img_cdn: bool, product_img_ext: string, product_img_root: bool } $productImage */
        foreach($data->getProductImages() as $productImage)
        {
            if(true === $productImage->product_img_root)
            {
                continue;
            }

            $picture = sprintf(
                'https://%s%s/%s.%s',
                $productImage->product_img_cdn ? $this->CDN_HOST : $this->HOST,
                $productImage->product_img,
                $productImage->product_img_cdn ? 'large' : 'image',
                $productImage->product_img_ext,
            );

            // Проверяем доступность файла изображения
            $Headers = get_headers($picture);

            if(false === $Headers)
            {
                continue;
            }

            $Headers = current($Headers);

            if(str_contains($Headers, '200')) // ожидаем HTTP/1.1 200 OK
            {
                $result[] = $picture;
            }
        }

        return empty($result) ? [] : $result;

        //        if(!empty($data['product_images']))
        //        {
        //            foreach(json_decode($data['product_images'], true) as $item)
        //            {
        ////                if($item['product_photo_root'] === true)
        ////                {
        ////                    continue;
        ////                }
        //
        //                $picture = sprintf(
        //                    'https://%s%s/%s.%s',
        //                    $item['product_photo_cdn'] ? $this->CDN_HOST : $this->HOST,
        //                    $item['product_photo_name'],
        //                    $item['product_photo_cdn'] ? 'large' : 'image',
        //                    $item['product_photo_ext']
        //                );
        //
        //                // Проверяе м доступность файла изображения
        //                $Headers = get_headers($picture);
        //
        //                if(false === $Headers)
        //                {
        //                    continue;
        //                }
        //
        //                $Headers = current($Headers);
        //
        //                if(str_contains($Headers, '200')) // ожидаем HTTP/1.1 200 OK
        //                {
        //                    $result[] = $picture;
        //                }
        //            }
        //        }
        //
        //        return $result;
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
