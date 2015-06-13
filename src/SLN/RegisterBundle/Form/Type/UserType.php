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
          ->add('email', 'email', array('label' => 'Adresse email'))
          ->add('username', null, array('label' => 'Nom d\'utilisateur'))
          ->add('titre', 'choice', array(
                'choices' => User::getTitres(),))
          ->add('nom')
          ->add('prenom')
          ->add('adresse')
          ->add('code_postal')
          ->add('ville')
          ->add('tel_domicile')
          ->add('tel_portable');
    }

    public function getName()
    {
        return 'sln_registerbundle_usertype';
    }
}

