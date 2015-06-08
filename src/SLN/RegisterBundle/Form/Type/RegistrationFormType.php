<?php

namespace SLN\RegisterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use SLN\RegisterBundle\Entity\User;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // add your custom field
        $builder->add('nom')
                ->add('prenom')
                ->add('titre', 'choice', array(
                  'choices' => User::getTitres(),))
                ->add('adresse')
                ->add('code_postal')
                ->add('ville')
                ->add('tel_domicile')
                ->add('tel_portable');
    }

    public function getParent()
    {
        return 'fos_user_registration';
    }

    public function getName()
    {
        return 'sln_user_registration';
    }
}
