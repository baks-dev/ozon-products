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

namespace BaksDev\Ozon\Products\Controller\Admin\Settings;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Ozon\Products\Entity\Settings\OzonProductsSettings;
use BaksDev\Ozon\Products\UseCase\Settings\NewEdit\OzonProductsSettingsDTO;
use BaksDev\Ozon\Products\UseCase\Settings\NewEdit\OzonProductsSettingsForm;
use BaksDev\Ozon\Products\UseCase\Settings\NewEdit\OzonProductsSettingsHandler;
use BaksDev\Products\Category\Entity\CategoryProduct;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_OZON_PRODUCTS_NEW')]
final class NewController extends AbstractController
{
    #[Route('/admin/ozon/product/setting/new/{category}/{ozon<\d+>}/{type<\d+>}', name: 'admin.settings.newedit.new', methods: ['GET', 'POST',])]
    public function new(
        Request $request,
        OzonProductsSettingsHandler $productsSettingsHandler,
        #[MapEntity] CategoryProduct $category,
        int $ozon,
        int $type,
    ): Response
    {

        $SettingsDTO = new OzonProductsSettingsDTO();
        $SettingsDTO->setSettings($category->getId());
        $SettingsDTO->setOzon($ozon);
        $SettingsDTO->setType($type);

        /* Форма добавления */
        $form = $this->createForm(OzonProductsSettingsForm::class, $SettingsDTO);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('product_settings'))
        {
            $this->refreshTokenForm($form);

            $handle = $productsSettingsHandler->handle($SettingsDTO);

            if($handle instanceof OzonProductsSettings)
            {
                $this->addFlash('page.new', 'success.new', 'ozon-products.admin.settings');

                return $this->redirectToRoute('ozon-products:admin.settings.index');
            }

            $this->addFlash('page.new', 'danger.new', 'ozon-products.admin.settings', $handle);

            return $this->redirectToReferer();
        }

        return $this->render([
            'form' => $form->createView()
        ]);

    }
}
