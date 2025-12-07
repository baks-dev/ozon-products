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

namespace BaksDev\Ozon\Products\Controller\Admin\Barcode;

use BaksDev\Barcode\Writer\BarcodeFormat;
use BaksDev\Barcode\Writer\BarcodeType;
use BaksDev\Barcode\Writer\BarcodeWrite;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Core\Type\UidType\ParamConverter;
use BaksDev\Ozon\Products\Repository\Barcode\OzonBarcodeSettings\OzonBarcodeSettingsInterface;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByEventInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_OZON_BARCODE_PRINT')]
final class PrintController extends AbstractController
{
    /**
     * Штрихкод Ozon карточки
     */
    #[Route('/admin/ozon/barcode/print/{product}/{offer}/{variation}/{modification}', name: 'admin.barcode.print', methods: ['GET'])]
    public function index(
        Request $request,
        #[Target('OzonProductsLogger')] LoggerInterface $logger,
        OzonBarcodeSettingsInterface $OzonBarcodeSettings,
        ProductDetailByEventInterface $ProductDetailByUid,
        BarcodeWrite $BarcodeWrite,
        #[ParamConverter(ProductEventUid::class, key: 'product')] ProductEventUid $event,
        #[ParamConverter(ProductOfferUid::class, key: 'offer')] ?ProductOfferUid $offer = null,
        #[ParamConverter(ProductVariationUid::class, key: 'variation')] ?ProductVariationUid $variation = null,
        #[ParamConverter(ProductModificationUid::class, key: 'modification')] ?ProductModificationUid $modification = null,
    ): Response
    {
        /**
         * Получаем информацию о продукте
         */

        $ProductDetail = $ProductDetailByUid
            ->event($event)
            ->offer($offer)
            ->variation($variation)
            ->modification($modification)
            ->find();

        if(!$ProductDetail)
        {
            $logger->critical(
                'ozon-products: Продукция в упаковке не найдена',
                [
                    'event' => $event,
                    'offer' => $offer,
                    'variation' => $variation,
                    'modification' => $modification,
                    self::class.':'.__LINE__]
            );

            return new Response('Продукция в упаковке не найдена', Response::HTTP_NOT_FOUND);
        }

        /**
         * Генерируем штрихкод продукции (один на все заказы)
         */
        $barcode = $BarcodeWrite
            ->text($ProductDetail['product_barcode'])
            ->type(BarcodeType::Code128)
            ->format(BarcodeFormat::SVG)
            ->generate();

        if($barcode === false)
        {
            /**
             * Проверить права на исполнение
             * chmod +x /home/bundles.baks.dev/vendor/baks-dev/barcode/Writer/Generate
             * chmod +x /home/bundles.baks.dev/vendor/baks-dev/barcode/Reader/Decode
             * */
            throw new RuntimeException('Barcode write error');
        }

        $render = $BarcodeWrite->render();
        $BarcodeWrite->remove();
        $render = strip_tags($render, ['path']);
        $render = trim($render);

        /**
         * Получаем настройки бокового стикера
         */
        $BarcodeSettings = $ProductDetail['main'] ?
            $OzonBarcodeSettings->forProduct($ProductDetail['main'])->find() : false;

        return $this->render(
            parameters: [
                'barcode' => $render,
                'settings' => $BarcodeSettings,
                'total' => $request->get('total', 1),
                'product' => $ProductDetail,
            ],
            file: 'print.html.twig'
        );
    }
}