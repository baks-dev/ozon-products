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

namespace BaksDev\Ozon\Products\Controller\Admin\Barcode;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Ozon\Products\Entity\Barcode\Event\OzonBarcodeEvent;
use BaksDev\Ozon\Products\Entity\Barcode\OzonBarcode;
use BaksDev\Ozon\Products\UseCase\Barcode\NewEdit\OzonBarcodeDTO;
use BaksDev\Ozon\Products\UseCase\Barcode\NewEdit\OzonBarcodeForm;
use BaksDev\Ozon\Products\UseCase\Barcode\NewEdit\OzonBarcodeHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_OZON_BARCODE_EDIT')]
final class EditController extends AbstractController
{
    #[Route('/admin/ozon/barcode/edit/{id}', name: 'admin.barcode.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity] OzonBarcodeEvent $Event,
        OzonBarcodeHandler $OzonBarcodeHandler,
    ): Response
    {
        $OzonBarcodeDTO = new OzonBarcodeDTO();
        $OzonBarcodeDTO->hiddenCategory();
        $Event->getDto($OzonBarcodeDTO);

        $form = $this->createForm(
            type: OzonBarcodeForm::class,
            data: $OzonBarcodeDTO,
            options: [
                'action' => $this->generateUrl('ozon-products:admin.barcode.newedit.edit',
                    ['id' => $OzonBarcodeDTO->getEvent()]),
            ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('ozon_barcode'))
        {
            $handle = $OzonBarcodeHandler->handle($OzonBarcodeDTO, $this->getProfileUid());

            $this->addFlash
            (
                'admin.page.edit',
                $handle instanceof OzonBarcode ? 'admin.success.edit' : 'admin.danger.edit',
                'ozon-products.barcode',
                $handle
            );

            return $this->redirectToRoute('ozon-products:admin.barcode.index');
        }

        return $this->render(['form' => $form->createView()]);

    }

}