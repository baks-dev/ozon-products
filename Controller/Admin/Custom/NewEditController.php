<?php
/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Products\Controller\Admin\Custom;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByInvariableInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use BaksDev\Ozon\Products\Entity\Custom\OzonProductCustom;
use BaksDev\Ozon\Products\UseCase\NewEdit\OzonCustomProductHandler;
use BaksDev\Ozon\Products\UseCase\NewEdit\OzonCustomProductDTO;
use BaksDev\Ozon\Products\UseCase\NewEdit\OzonCustomProductForm;


#[AsController]
#[RoleSecurity('ROLE_OZON_PRODUCTS_EDIT')]
class NewEditController extends AbstractController
{
    #[Route(
        '/admin/ozon/custom/edit/{invariable}',
        name: 'admin.custom.edit',
        methods: ['GET', 'POST']
    )]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        OzonCustomProductHandler $OzonProductHandler,
        ProductDetailByInvariableInterface $productDetailByInvariable,
        string|null $invariable = null,
    ): Response
    {
        $ozonCustomProductDTO = new OzonCustomProductDTO()
            ->setInvariable($invariable);

        /**
         * Находим уникальный продукт Озон, делаем его инстанс, передаем в форму
         *
         * @var OzonProductCustom|null $ozonProductCard
         */
        $ozonProductCard = $entityManager
            ->getRepository(OzonProductCustom::class)
            ->findOneBy(['invariable' => $invariable]);

        $ozonProductCard?->getDto($ozonCustomProductDTO);

        $form = $this
            ->createForm(
                type: OzonCustomProductForm::class,
                data: $ozonCustomProductDTO,
                options: ['action' => $this->generateUrl(
                    'ozon-products:admin.custom.edit', ['invariable' => $ozonCustomProductDTO->getInvariable(),],
                )],
            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('ozon_product'))
        {
            $this->refreshTokenForm($form);

            $handle = $OzonProductHandler->handle($ozonCustomProductDTO);

            $this->addFlash(
                'page.edit',
                $handle instanceof OzonProductCustom ? 'success.edit' : 'danger.edit',
                'ozon-products.admin.custom',
                $handle,
            );

            return $this->redirectToRoute('ozon-products:admin.custom.index');
        }

        $ozonProduct = $productDetailByInvariable
            ->invariable($invariable)
            ->find();

        return $this->render([
            'form' => $form->createView(),
            'product' => $ozonProduct,
        ]);
    }


}