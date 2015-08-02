<?php
/**
  * Form type for inlined Horaire in Groupe form
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use SLN\RegisterBundle\Entity\Horaire;

/**
 * Horaire type class
 */
class HoraireType extends AbstractType {
   /**
    * Build the form
    *
    * @param FormBuilderInterface $builder
    * @param array $options
    */
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
            ->add('description', null, array("attr" => array("placeholder" => "Natation ou PPG ?")));
    }

    /** @ignore */
    public function getName()
    {
        return 'horaire';
    } 
}

