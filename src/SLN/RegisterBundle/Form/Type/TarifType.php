<?php
/**
  * Form type for inlined Tarif in Groupe form
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use SLN\RegisterBundle\Entity\Tarif;
use SLN\RegisterBundle\Entity\UserPayment;
use SLN\RegisterBundle\Form\DataTransformer\PriceTransformer;

/**
 * Tarif type class
 */
class TarifType extends AbstractType {
   /**
    * Build the form
    *
    * @param FormBuilderInterface $builder
    * @param array $options
    */
   public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', array('choices' => Tarif::getTypes(),))
            ->add('value', 'text', array('label' => 'Valeur'))
            ->add('description', 'text', array('label' => 'Description'));

        $builder->get('value')->addModelTransformer(new PriceTransformer());
    }

    /** @ignore */
    public function getName()
    {
        return 'type';
    } 
}


