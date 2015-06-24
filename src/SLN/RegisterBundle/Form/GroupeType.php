<?php

namespace SLN\RegisterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Form\Type\HoraireType;


class GroupeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', null, array("attr" => array("placeholder" => "Nom du groupe")))
            ->add('categorie', 'choice', array(
                  'choices' => Groupe::getCategories(),))
            ->add('description', 'textarea')
            ->add('horaires', 'collection', array(
                  'type' => new HoraireType(),
                  'prototype' => true,
                  'allow_add' => true,
                  'allow_delete' => true,
                  'by_reference' => false,
                  'options' => array()
                ));
    }

    public function getName()
    {
        return 'sln_registerbundle_groupetype';
    }
}

