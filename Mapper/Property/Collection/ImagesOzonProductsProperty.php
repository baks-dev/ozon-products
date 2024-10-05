<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Property\Collection;

use BaksDev\Ozon\Products\Mapper\Property\OzonProductsPropertyInterface;
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

    public const PARAM = 'images';

    public function __construct(
        #[Autowire(env: 'HOST')]
        private readonly ?string $HOST = null,
        #[Autowire(env: 'CDN_HOST')]
        private readonly ?string $CDN_HOST = null,
    ) {
    }

    public function getValue(): string
    {
        return self::PARAM;
    }

    /**
     * Возвращает состояние
     */
    public function getData(array $data): array
    {
        $result = [];

        if(!empty($data['product_images']))
        {
            foreach (json_decode($data['product_images'], true) as $item)
            {
                if($item['product_photo_root'] === true)
                {
                    continue;
                }

                $picture = sprintf(
                    'https://%s%s/%s.%s',
                    $item['product_photo_cdn'] ? $this->CDN_HOST : $this->HOST,
                    $item['product_photo_name'],
                    $item['product_photo_cdn'] ? 'large' : 'image',
                    $item['product_photo_ext']
                );

                // Проверяе м доступность файла изображения
                $Headers = get_headers($picture);
                $Headers = current($Headers);

                if(str_contains($Headers, '200')) // ожидаем HTTP/1.1 200 OK
                {
                    $result[] = $picture;
                }
            }
        }

        return $result;
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
