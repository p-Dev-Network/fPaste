<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Username: ',
                    'class' => 'form-control is-valid mb20'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'label' => true,
                'required' => true,
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Passwords not match.',
                'first_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Password: ',
                        'class' => 'form-control is-valid mb20'
                    ]
                ],
                'second_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Repeat password: ',
                        'class' => 'form-control is-valid mb20'
                    ]
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Email: ',
                    'class' => 'form-control is-valid mb20'
                ]
            ])
            ->add('SignUp', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-send'
                ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User'
        ));
    }
}