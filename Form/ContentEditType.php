<?php

namespace Maith\NewsletterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContentEditType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('title')
            ->add('body')
            //->add('createdat')
            //->add('updatedat')
            //->add('active')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Maith\NewsletterBundle\Entity\Content',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'maith_newsletterbundle_content';
    }
}
