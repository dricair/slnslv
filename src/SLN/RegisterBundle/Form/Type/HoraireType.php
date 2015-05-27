<?php

namespace SLN\RegisterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use SLN\RegisterBundle\Entity\Horaire;


class HoraireType extends AbstractType {
   public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('jour', 'choice', array('choices' => Horaire::getJours(),))
            ->add('debut', 'time', array(
                'input' => 'timestamp',
                'widget' => 'single_text',
                'with_seconds' => False))
            ->add('fin', 'time', array(
                'input' => 'timestamp',
                'widget' => 'single_text',
                'with_seconds' => False))
            ->add('description');
    }

    public function getName()
    {
        return 'horaire';
    } 
}

