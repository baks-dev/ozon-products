<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Products\Controller\Admin\Settings;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Ozon\Products\Form\Preform\PreformDTO;
use BaksDev\Ozon\Products\Form\Preform\PreformForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_OZON_PRODUCTS_NEW')]
final class PreformController extends AbstractController
{
    #[Route('/admin/ozon/product/settings/preform', name: 'admin.settings.preform', methods: ['POST', 'GET'])]
    public function preform(
        Request $request,
    ): Response {

        $PreformDTO = new PreformDTO();

        $form = $this->createForm(PreformForm::class, $PreformDTO, [
            'action' => $this->generateUrl('ozon-products:admin.settings.preform'),
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('ozon_preform'))
        {

            $this->refreshTokenForm($form);
            return $this->redirectToRoute(
                'ozon-products:admin.settings.newedit.new',
                [
                    'category'  => $PreformDTO->getCategory(),
                    'ozon'  => $PreformDTO->getOzon()?->getId(),
                    'type'      => $PreformDTO->getType()?->getId()
                ]
            );
        }

        return $this->render(['form' => $form->createView()]);
    }

}
