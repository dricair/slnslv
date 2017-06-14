<?php
/**
 * Create a form to change the new group field
 */

namespace SLN\RegisterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use SLN\RegisterBundle\Entity\LicenseeSaison;
use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Repository\GroupeRepository;

class LicenseeSaisonNewGroupeType extends AbstractType
{
    /**
    * Build the form
    *
    * @param FormBuilderInterface $builder
    * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('new_groupe', 'entity', array("class" => "SLNRegisterBundle:Groupe",
                                                    "group_by" => 'categorieName'));
    }

    /**
     * Set default options to set to the instance
     *
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => LicenseeSaison::class,
        ));
    }

    /** @ignore */
    public function getName()
    {
        return 'sln_registerbundle_licenseesaison_changegroupe_type';
    }

}



