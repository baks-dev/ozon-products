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

namespace BaksDev\Ozon\Products\UseCase\Settings\NewEdit;

use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeCollection;
use BaksDev\Ozon\Products\Mapper\Attribute\OzonProductsAttributeInterface;
use BaksDev\Ozon\Products\Mapper\Property\OzonProductPropertyCollection;
use BaksDev\Ozon\Products\Mapper\Property\OzonProductsPropertyInterface;
use BaksDev\Ozon\Products\Type\Settings\Attribute\OzonProductAttribute;
use BaksDev\Ozon\Products\Type\Settings\Property\OzonProductProperty;
use BaksDev\Ozon\Products\UseCase\Settings\NewEdit\Attributes\OzonProductsSettingsAttributeDTO;
use BaksDev\Ozon\Products\UseCase\Settings\NewEdit\Properties\OzonProductsSettingsPropertyDTO;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\ModificationCategoryProductSectionField\ModificationCategoryProductSectionFieldInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\OffersCategoryProductSectionField\OffersCategoryProductSectionFieldInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\PropertyFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\VariationCategoryProductSectionField\VariationCategoryProductSectionFieldInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OzonProductsSettingsForm extends AbstractType
{
    public function __construct(
        private readonly OffersCategoryProductSectionFieldInterface $offersCategoryProductSectionField,
        private readonly VariationCategoryProductSectionFieldInterface $variationCategoryProductSectionField,
        private readonly ModificationCategoryProductSectionFieldInterface $modificationCategoryProductSectionField,
        private readonly PropertyFieldsCategoryChoiceInterface $propertyFields,
        private readonly OzonProductPropertyCollection $ozonProductPropertyCollection,
        private readonly OzonProductsAttributeCollection $ozonProductAttributeCollection
    ) {}


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

            /** @var OzonProductsSettingsDTO $data */
            $data = $event->getData();
            $form = $event->getForm();

            /** Коллекция свойств категории для выпадающего списка */
            $property_fields = $this->propertyFields
                ->category($data->getSettings())
                ->getPropertyFieldsCollection();

            /**  Добавляем к выбору ТП, варианты и модификации */
            //$offer = $this->propertyFields->getOffersFields($data->getSettings());
            $offer = $this->offersCategoryProductSectionField
                ->category($data->getSettings())
                ->findAllCategoryProductSectionField();

            if($offer)
            {
                array_unshift($property_fields, $offer);

                $variation = $this->variationCategoryProductSectionField
                    ->offer($offer->getValue())
                    ->findAllCategoryProductSectionField();

                if($variation)
                {
                    array_unshift($property_fields, $variation);

                    $modification = $this->modificationCategoryProductSectionField
                        ->variation($variation->getValue())
                        ->findAllCategoryProductSectionField();

                    if($modification)
                    {
                        array_unshift($property_fields, $modification);
                    }
                }
            }

            /**
             * Свойства карточки Ozon
             * @var OzonProductsPropertyInterface $case
             */
            foreach($this->ozonProductPropertyCollection->casesSettings() as $case)
            {
                $OzonProductSettingsPropertyDTO = new OzonProductsSettingsPropertyDTO();
                $OzonProductSettingsPropertyDTO->setType(new OzonProductProperty($case));
                $data->addProperty($OzonProductSettingsPropertyDTO);
            }

            $form->add('properties', CollectionType::class, [
                'entry_type' => Properties\OzonProductsSettingsPropertyForm::class,
                'entry_options' => ['label' => false, 'property_fields' => $property_fields],
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
            ]);

            /**
             * Аттрибуты карточки Ozon
             * @var OzonProductsAttributeInterface $case
             */
            foreach($this->ozonProductAttributeCollection->casesSettings($data->getOzon()) as $case)
            {
                $OzonProductSettingsAttributeDTO = new OzonProductsSettingsAttributeDTO();
                $OzonProductSettingsAttributeDTO->setType(new OzonProductAttribute($case));
                $data->addAttribute($OzonProductSettingsAttributeDTO);
            }


            $form->add('attributes', CollectionType::class, [
                'entry_type' => Attributes\OzonProductsSettingsAttributeForm::class,
                'entry_options' => ['label' => false, 'property_fields' => $property_fields],
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
            ]);

        });

        /* Сохранить ******************************************************/
        $builder->add(
            'product_settings',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']],
        );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OzonProductsSettingsDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }

}
