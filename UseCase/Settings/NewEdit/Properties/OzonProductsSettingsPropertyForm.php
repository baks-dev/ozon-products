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

namespace BaksDev\Ozon\Products\UseCase\Settings\NewEdit\Properties;

//use App\Module\Products\Category\Repository\PropertyFieldsByCategoryChoiceForm\PropertyFieldsByCategoryChoiceFormInterface;
//use App\Module\Products\Category\Type\Id\CategoryUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OzonProductsSettingsPropertyForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {

            /** @var OzonProductsSettingsPropertyDTO $data */
            $data = $event->getData();
            $form = $event->getForm();

            if($data)
            {
                $ozonProperty = $data->getType()?->getOzonProductProperty();

                $form
                    ->add('field', ChoiceType::class, [
                        'choices' => $options['property_fields'],
                        'choice_value' => function ($type) {
                            return $type?->getValue();
                        },
                        'choice_label' => function ($type) {
                            return $type->getAttr();
                        },

                        'label' => $data->getType() .'.name',
                        'help' => $data->getType() .'.desc',
                        'expanded' => false,
                        'multiple' => false,
                        'translation_domain' => 'ozon-products.mapper',
                        'required' => false,
                        //'disabled' => !$data->isIsset()
                    ]);


                if($ozonProperty && $ozonProperty->choices())
                {
                    $form
                        ->add('def', ChoiceType::class, [
                            'choices' =>  $ozonProperty->choices(),
                            'expanded' => false,
                            'multiple' => false,
                            'translation_domain' => 'ozon-products.property',
                            'data' => $data->getDef() ?: $ozonProperty->default(),
                            'required' => $ozonProperty->required(),
                        ]);
                }
                else
                {
                    $form->add(
                        'def',
                        TextType::class,
                        [
                            'data' => $data->getDef() ?: $ozonProperty->default(),
                            'required' => $ozonProperty->required()
                        ]
                    );
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => OzonProductsSettingsPropertyDTO::class,
                'property_fields' => null,
            ]
        );
    }

}
