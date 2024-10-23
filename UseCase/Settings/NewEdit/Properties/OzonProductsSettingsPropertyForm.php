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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($options) {

            /** @var OzonProductsSettingsPropertyDTO $data */
            $data = $event->getData();
            $form = $event->getForm();

            if($data)
            {
                $ozonProperty = $data->getType()?->getOzonProductProperty();

                $form
                    ->add('field', ChoiceType::class, [
                        'choices' => $options['property_fields'],
                        'choice_value' => function($type) {
                            return $type?->getValue();
                        },
                        'choice_label' => function($type) {
                            return $type->getAttr();
                        },

                        'label' => $data->getType().'.name',
                        'help' => $data->getType().'.desc',
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
                            'choices' => $ozonProperty->choices(),
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
