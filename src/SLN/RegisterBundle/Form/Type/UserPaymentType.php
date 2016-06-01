<?php
/**
 * Form to create or update a payment
 */

namespace SLN\RegisterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use SLN\RegisterBundle\Entity\User;
use SLN\RegisterBundle\Entity\UserPayment;
use SLN\RegisterBundle\Entity\Repository\UserRepository;
use SLN\RegisterBundle\Form\DataTransformer\PriceTransformer;


/**
 * Form to create or update a payment
 */
class UserPaymentType extends AbstractType
{
   /**
    * Build the form
    *
    * @param FormBuilderInterface $builder
    * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('ptype', 'choice', array('choices' => UserPayment::getTypes(1), 
                                               'label' => 'Type de paiement'))
                ->add('description', 'text')
                ->add('value', 'text', array('label' => 'Valeur'));

        $builder->get('value')->addModelTransformer(new PriceTransformer());
    }

    /** @ignore */
    public function getName()
    {
        return 'sln_registerbundle_userpayment';
    }
    
}

