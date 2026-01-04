<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Products\Form\Preform;

use BaksDev\Ozon\Products\Api\Settings\Category\OzonCategoryDTO;
use BaksDev\Ozon\Products\Mapper\Category\OzonProductsCategoryCollection;
use BaksDev\Ozon\Products\Mapper\Category\OzonProductsCategoryInterface;
use BaksDev\Ozon\Products\Mapper\Type\OzonProductsTypeCollection;
use BaksDev\Ozon\Products\Mapper\Type\OzonProductsTypeInterface;
use BaksDev\Products\Category\Repository\CategoryChoice\CategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PreformForm extends AbstractType
{
    public function __construct(
        private readonly CategoryChoiceInterface $categoryChoice,
        private readonly OzonProductsCategoryCollection $ozonProductCategoryCollection,
        private readonly OzonProductsTypeCollection $ozonProductTypeCollection
    ) {}


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** Список локальных категорий */

        $builder
            ->add(
                'category',
                ChoiceType::class,
                [
                    'choices' => $this->categoryChoice->findAll(),
                    'choice_value' => function(?CategoryProductUid $type) {
                        return $type?->getValue();
                    },
                    'choice_label' => function(CategoryProductUid $type) {
                        return $type->getOptions();
                    },

                    'label' => false,
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                ]
            );


        /** Список родительских категорий Ozon */
        $builder->add(
            'ozon',
            ChoiceType::class,
            [
                'choices' => $this->ozonProductCategoryCollection->casesSettings(),
                'choice_value' => function(?OzonProductsCategoryInterface $ozonCategory) {
                    return $ozonCategory?->getId();
                },
                'choice_label' => function(OzonProductsCategoryInterface $ozonCategory) {
                    return $ozonCategory->getId().'.name';
                },
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'translation_domain' => 'ozon-products.mapper',
            ]
        );


        /** Определяем пустой список категорий с типами продуктов Ozon */
        $builder->add(
            'type',
            ChoiceType::class,
            [
                'choices' => [],
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'disabled' => true,
            ]
        );

        $formModifier = function(FormInterface $form, ?int $ozonCategoryId = null): void {

            if($ozonCategoryId === null)
            {
                return;
            }

            /** Список типов продуктов Ozon */
            $form->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => $this->ozonProductTypeCollection->casesSettings($ozonCategoryId),
                    'choice_value' => function(?OzonProductsTypeInterface $ozonType) {
                        return $ozonType?->getId();
                    },
                    'choice_label' => function(OzonProductsTypeInterface $ozonType) {
                        return $ozonType->getId().'.name';
                    },
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                    'placeholder' => 'Выберите тип из свойства',
                    'translation_domain' => 'ozon-products.mapper'
                ]
            );
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function(FormEvent $event) use ($formModifier): void {
                $data = $event->getData();
                /** @var PreformDTO $data */
                $formModifier($event->getForm(), $data->getType());
            }
        );


        $builder->get('ozon')->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) use ($formModifier): void {
                /** @var OzonCategoryDTO $ozonCategory */
                $ozonCategory = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $ozonCategory->getId());
            }
        );


        /* Сохранить ******************************************************/
        $builder->add(
            'ozon_preform',
            SubmitType::class,
            ['label_html' => true, 'attr' => ['class' => 'btn-primary']],
        );
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => PreformDTO::class,
                'method' => 'POST',
                'attr' => ['class' => 'w-100'],
            ],
        );
    }
}
