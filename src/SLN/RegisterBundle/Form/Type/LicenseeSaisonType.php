<?php
/**
 * Create a form for a Groupe instance
 */

namespace SLN\RegisterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use SLN\RegisterBundle\Entity\Horaire;
use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Saison;
use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\LicenseeSaison;
use SLN\RegisterBundle\Entity\Repository\GroupeRepository;


/**
 * Create a form for a LicenseeForm instance
 */
class LicenseeSaisonType extends AbstractType
{
   /**
    * Build the form
    *
    * @param FormBuilderInterface $builder
    * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $admin = $options["admin"];
        $defaultGroupe = $options["defaultGroupe"];

        $builder->add('groupe_jours', 'choice', array(
                      'label' => 'Choix des jours',
                      'choices' => Horaire::getJours(),
                      'multiple' => true,
                      'expanded' => true));

        if ($options["admin"])
            $builder->add('groupe', 'entity', array("class" => "SLNRegisterBundle:Groupe",
                                                    "group_by" => 'categorieName'))
                    ->add('inscription', 'choice', array(
                          'label' => 'Etat de l\'inscription',
                          'choices' => LicenseeSaison::getInscriptionNames(),
                          'multiple' => true,
                          'expanded' => true));
        else {
            $builder->add('groupe', 'entity', array("class" => "SLNRegisterBundle:Groupe",
                                                    "group_by" => 'categorieName',
                                                    "empty_data" => null,
                                                    "query_builder" => function (GroupeRepository $er) use ($defaultGroupe) {
                                                       return $er->findLicenseePublic($defaultGroupe, TRUE);
                                                 }));
        }
    }

    /**
     * Set default options to set to the instance
     *
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'admin' => false,
            'defaultGroupe' => null,
            'data_class' => LicenseeSaison::class,
        ));
    }

    /** @ignore */
    public function getName()
    {
        return 'sln_registerbundle_licenseesaisontype';
    }
}


