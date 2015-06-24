<?php

namespace SLN\RegisterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EnquiryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', null, array('label' => 'Nom'));
        $builder->add('email', 'email', array('label' => 'Prénom'));
        $builder->add('subject', null, array('label' => 'Sujet'));
        $builder->add('body', 'textarea', array('label' => 'Message'));
    }

    public function getName()
    {
        return 'contact';
    }
}
