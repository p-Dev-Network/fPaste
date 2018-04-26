<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class SupportType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Your Email: ',
                    'class' => 'form-control is-valid'
                ]
            ])
            ->add('category', ChoiceType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-control is-valid'
                ],
                'choices' => [
                    'Abuse' => 'abuse',
                    'Technical Problems' => 'technicalProblems',
                    'Other' => 'other'
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-control is-valid',
                    'placeholder' => 'Write your message: ',
                    'rows' => '10'
                ]
            ])
            ->add('Send', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-send is-valid'
                ]
            ]);
    }
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Support'
        ));
    }
}