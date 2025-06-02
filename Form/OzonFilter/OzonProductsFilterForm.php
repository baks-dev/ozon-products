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

namespace BaksDev\Ozon\Products\Form\OzonFilter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class OzonProductsFilterForm extends AbstractType
{
    private string $sessionKey;

    private SessionInterface|false $session = false;

    public function __construct(private readonly RequestStack $request)
    {
        $this->sessionKey = md5(self::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('exists', ChoiceType::class, [
            'choices' => [
                'Все' => null,
                'С фото' => true,
                'Без фото' => false,
            ],
        ]);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function(FormEvent $event): void {
                /** @var OzonProductsFilterDTO $data */
                $data = $event->getData();

                if($this->session === false)
                {
                    $this->session = $this->request->getSession();
                }

                if($this->session && $this->session->get('statusCode') === 307)
                {
                    $this->session->remove($this->sessionKey);
                    $this->session = false;
                }

                if($this->session)
                {
                    if(time() - $this->session->getMetadataBag()->getLastUsed() > 300)
                    {
                        $this->session->remove($this->sessionKey);
                        $data->setExists(null);
                        return;
                    }

                    $sessionData = $this->request->getSession()->get($this->sessionKey);
                    $sessionJson = $sessionData ? base64_decode($sessionData) : false;
                    $sessionArray = $sessionJson !== false && json_validate($sessionJson) ? json_decode($sessionJson, true) : [];

                    $data->setExists($sessionArray['exists'] ?? null);
                }
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event): void {
                /** @var OzonProductsFilterDTO $data */
                $data = $event->getData();

                if($this->session === false)
                {
                    $this->session = $this->request->getSession();
                }

                if($this->session)
                {
                    $sessionArray = [];

                    $data->getExists() !== null ? $sessionArray['exists'] = $data->getExists() : null;

                    if($sessionArray)
                    {
                        $sessionJson = json_encode($sessionArray);
                        $sessionData = base64_encode($sessionJson);
                        $this->request->getSession()->set($this->sessionKey, $sessionData);
                        return;
                    }

                    $this->request->getSession()->remove($this->sessionKey);

                }
            }
        );
    }
}