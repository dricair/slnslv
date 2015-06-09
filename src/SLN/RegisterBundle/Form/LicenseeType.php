<?php

namespace SLN\RegisterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use SLN\RegisterBundle\Entity\Licensee;


class LicenseeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('naissance', 'date', array('years' => range(date('Y')-100, date('Y')-3)))
            ->add('sexe', 'choice', array(
                  'choices' => Licensee::getGenders(),))
            ->add('groupe', null, array("group_by" => 'categorieName'))
            ->add('iuf')
            ->add('officiel')
            ->add('bureau')
            ->add('autorisation_photos')
        ;
    }

    public function getName()
    {
        return 'sln_registerbundle_licenseetype';
    }
}

