<?php

namespace Maith\NewsletterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentSendType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('title')
            //->add('body')
            ->add('sendat', null, array(
                  'widget' => 'single_text',
                  'format' => 'dd-MM-yyyy',
                  )
            )
            //->add('createdat')
            //->add('active')
            //->add('quantitySended')
            //->add('sended')
            //->add('content')
            ->add('emailLayout')
            ->add('sendToType', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', array(
                      'mapped' => false,
                      'choices_as_values' => true,
                      'choices' => array(
                            'Todos' => 1,
                            'Grupos' => 2,
                            'Usuarios' => 3,
                          ),
                      'choice_value' => function ($choice) {
                           return $choice;
                      },
            ))
            ->add('sendlist', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', array(
                      'mapped' => false,
            ))
            ->add('sendlistIds', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', array(
                      'mapped' => false,
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Maith\NewsletterBundle\Entity\ContentSend',
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'maith_newsletterbundle_contentsend';
    }
}
