<?php

namespace SLN\RegisterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use SLN\RegisterBundle\Entity\Licensee;


class LicenseeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $admin = $options["admin"];

        $builder
            ->add('nom', null, array("attr" => array("placeholder" => "Nom")))
            ->add('prenom', null, array("label" => "Prénom", "attr" => array("placeholder" => "Prénom")))
            ->add('naissance', 'date', array("widget" => "single_text", 'format' => 'dd/MM/yyyy', 
                                             "years" => range(date('Y')-100, date('Y')-3)))
            ->add('sexe', 'choice', array(
                  'choices' => Licensee::getGenders(),))
            ->add('groupe', null, array("group_by" => 'categorieName'))
            ->add('iuf', null, array("attr" => array("placeholder" => "01234567")))
            ->add('officiel', 'checkbox', array('required'=>false))
            ->add('bureau', 'checkbox', array('required'=>false))
            ->add('autorisation_photos', 'checkbox', array('required'=>false));

        if ($options["admin"])
            $builder->add('user', null, array("label" => "Rattaché au compte"));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array("admin" => false));
    }

    public function getName()
    {
        return 'sln_registerbundle_licenseetype';
    }
}

