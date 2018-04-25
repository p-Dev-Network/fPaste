<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Title (Optional): ',
                    'class' => ''
                ]
            ])
            ->add('content', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Insert your paste: ',
                    'class' => ''
                ]
            ])
            ->add('privacy', ChoiceType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => ''
                ],
                'choices' => [
                    'Public' => 'public',
                    'Private' => 'private'
                ]
            ])
            ->add('Send', SubmitType::class, [
                'attr' => [
                    'class' => ''
                ]
            ]);
    }
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Paste'
        ));
    }
}