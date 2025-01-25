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

namespace BaksDev\Ozon\Products\Controller\Public;


use BaksDev\Core\Controller\AbstractController;
use BaksDev\Ozon\Products\Api\Card\Identifier\GetOzonCardSkuRequest;
use BaksDev\Ozon\Repository\AllProfileToken\AllProfileOzonTokenInterface;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByValueInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class PageController extends AbstractController
{

    #[Route('/ozon/product/{url}/{offer}/{variation}/{modification}/{postfix}', name: 'public.product', methods: ['GET'])]
    public function index(
        Request $request,
        #[MapEntity(mapping: ['url' => 'url'])] ProductInfo $info,
        ProductDetailByValueInterface $productDetail,
        GetOzonCardSkuRequest $GetOzonCardSku,
        AllProfileOzonTokenInterface $allProfileToken,

        ?string $offer = null,
        ?string $variation = null,
        ?string $modification = null,
        ?string $postfix = null,
    ): Response
    {
        $productCard = $productDetail->fetchProductAssociative(
            $info->getProduct(),
            $offer,
            $variation,
            $modification,
            $postfix
        );

        $profiles = $allProfileToken
            ->onlyActiveToken()
            ->findAll();

        /** @var UserProfileUid $profile */
        foreach($profiles as $profile)
        {
            $sku = $GetOzonCardSku
                ->profile($profile)
                ->article($productCard['product_article'])
                ->find();

            if($sku)
            {
                return new RedirectResponse(sprintf('https://ozon.ru/product/%s', $sku));
            }
        }

        $this->addFlash('Ozon', 'продукт не найден');

        return $this->redirectToReferer();
    }
}
