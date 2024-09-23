<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
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
    ) {
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OzonProductsSettingsDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }

}
