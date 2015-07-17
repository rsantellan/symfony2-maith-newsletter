<?php

namespace Maith\NewsletterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContentSendType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('title')
            //->add('body')
            ->add('sendat', null, array(
                  'widget'=> 'single_text',
                  'format' => 'dd-MM-yyyy',
                  )
            )
            //->add('createdat')
            //->add('active')
            //->add('quantitySended')
            //->add('sended')
            //->add('content')
            ->add('sendToType', 'choice', array(
                      'mapped' => false,
                      'choices' => array(
                            1=> "Todos",
                            2=> "Grupos",
                            3=> "Usuarios",
                          )
            ))
            ->add('sendlist', 'hidden', array(
                      'mapped' => false
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Maith\NewsletterBundle\Entity\ContentSend'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'maith_newsletterbundle_contentsend';
    }
}
