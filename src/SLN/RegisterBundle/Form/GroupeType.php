<?php
/**
 * Create a form for a Groupe type
 */

namespace SLN\RegisterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Form\Type\HoraireType;
use SLN\RegisterBundle\Form\Type\TarifType;


/**
 * Create a form for a Groupe type
 */
class GroupeType extends AbstractType
{
   /**
    * Build the form
    *
    * @param FormBuilderInterface $builder
    * @param array $options
    */
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
                ))
            ->add('tarifs', 'collection', array(
                  'type' => new TarifType(),
                  'prototype' => true,
                  'allow_add' => true,
                  'allow_delete' => true,
                  'by_reference' => false,
                  'options' => array()
                ))
            ->add('multiple', null, array(
                  'label' => 'Horaires indépendents',
                  'required' => false));
    }

    /** @ignore */
    public function getName()
    {
        return 'sln_registerbundle_groupetype';
    }
}

