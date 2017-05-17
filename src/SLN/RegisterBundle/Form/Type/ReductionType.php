<?php
/**
  * Form type for inlined Reduction in Groupe form
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use SLN\RegisterBundle\Entity\Reduction;

/**
 * Reduction type class
 */
class ReductionType extends AbstractType {
   /**
    * Build the form
    *
    * @param FormBuilderInterface $builder
    * @param array $options
    */
   public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start', 'date', array(
                  'input' => 'timestamp',
                  'widget' => 'single_text',
                  'format' => 'dd/MM/yyyy'))
            ->add('reduction', null, array('label' => 'Réduction',
                                           'attr' => array('placeholder' => 'Réduction en %')));
    }

    /** @ignore */
    public function getName()
    {
        return 'reduction';
    } 
}


