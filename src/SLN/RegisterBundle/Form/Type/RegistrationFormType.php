<?php
/**
  * Overrides RegistrationFormType from FOS User to register User
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use SLN\RegisterBundle\Entity\User;

/**
  * Overrides RegistrationFormType from FOS User to register User
  */
class RegistrationFormType extends AbstractType
{
   /**
    * Build the form
    *
    * @param FormBuilderInterface $builder
    * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // add your custom field
        $builder->add('nom', null, array('attr' => array('placeholder' => 'Nom')))
                ->add('prenom', null, array('attr' => array('placeholder' => 'Prénom')))
                ->add('titre', 'choice', array(
                      'choices' => User::getTitres(),))
                ->add('adresse', null, array('attr' => array('placeholder' => 'Adresse postale')))
                ->add('code_postal', null, array('attr' => array('placeholder' => '06xxx')))
                ->add('ville', null, array('attr' => array('placeholder' => 'Ville')))
                ->add('tel_domicile', null, array('attr' => array('placeholder' => '04xxxxxxxx')))
                ->add('tel_portable', null, array('attr' => array('placeholder' => '06xxxxxxxx')));
    }

    /** @ignore */
    public function getParent()
    {
        return 'fos_user_registration';
    }

    /** @ignore */
    public function getName()
    {
        return 'sln_user_registration';
    }
}
