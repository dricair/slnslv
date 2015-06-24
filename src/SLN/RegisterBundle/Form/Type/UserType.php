<?php

namespace SLN\RegisterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use SLN\RegisterBundle\Entity\User;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Used for administration, does not set password
        $builder
          ->add('email', 'email', array('label' => 'Adresse email', 'attr' => array('placeholder' => 'email@provider.fr')))
          ->add('username', null, array('label' => 'Nom d\'utilisateur', 'attr' => array('placeholder' => 'username')))
          ->add('titre', 'choice', array(
                'choices' => User::getTitres(),))
          ->add('nom', null, array('attr' => array('placeholder' => 'Nom')))
          ->add('prenom', null, array('attr' => array('placeholder' => 'Prénom')))
          ->add('adresse', null, array('attr' => array('placeholder' => 'Adresse postale')))
          ->add('code_postal', null, array('attr' => array('placeholder' => '06xxx')))
          ->add('ville', null, array('attr' => array('placeholder' => 'Ville')))
          ->add('tel_domicile', null, array('attr' => array('placeholder' => '04xxxxxxxx')))
          ->add('tel_portable', null, array('attr' => array('placeholder' => '06xxxxxxxx')))
          ->add('roles', 'choice', array('label' => null,
                                         'choices' => array('ROLE_ADMIN' => 'Administrateur'),
                                         'multiple' => true,
                                         'expanded' => true));
    }

    public function getName()
    {
        return 'sln_registerbundle_usertype';
    }
}

