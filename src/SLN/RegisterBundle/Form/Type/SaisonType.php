<?php
/**
 * Create a form for a Saison type
 */

namespace SLN\RegisterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use SLN\RegisterBundle\Entity\Saison;
use SLN\RegisterBundle\Form\Type\ReductionType;


/**
 * Create a form for a Saison type
 */
class SaisonType extends AbstractType
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
            ->add('nom', null, array("attr" => array("placeholder" => "Nom de la saison")))
            ->add('start', 'date', array('label' => 'Début de la saison',
                                         'widget' => 'single_text',
                                         'format' => 'dd/MM/yyyy'))
            ->add('activated', null, array('label' => 'Saison active pour les inscriptions',
                                           'required' => false))
            ->add('reductions', 'collection', array(
                  'type' => new ReductionType(),
                  'prototype' => true,
                  'allow_add' => true,
                  'allow_delete' => true,
                  'by_reference' => false,
                  'options' => array()
                  ));
    }

    /** @ignore */
    public function getName()
    {
        return 'sln_registerbundle_saisontype';
    }
}

