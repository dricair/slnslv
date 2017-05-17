<?php
/**
 * Create a form for a Groupe instance
 */

namespace SLN\RegisterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use SLN\RegisterBundle\Entity\Member;
use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\Horaire;
use SLN\RegisterBundle\Entity\Saison;
use SLN\RegisterBundle\Entity\LicenseeSaison;
use SLN\RegisterBundle\Entity\Repository\UserRepository;
use SLN\RegisterBundle\Entity\Repository\GroupeRepository;

use SLN\RegisterBundle\Form\Type\LicenseeSaisonType;


/**
 * Create a form for a Groupe instance
 */
class LicenseeType extends AbstractType
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
        $public_groups = $options["public_groups"];

        $builder
            ->add('nom', null, array("attr" => array("placeholder" => "Nom")))
            ->add('prenom', null, array("label" => "Prénom", "attr" => array("placeholder" => "Prénom")))
            ->add('naissance', 'date', array("widget" => "single_text", 'format' => 'dd/MM/yyyy', 
                                             "years" => range(date('Y')-100, date('Y')-3)))
            ->add('sexe', 'choice', array(
                  'choices' => Licensee::getGenders(),))
            ->add('iuf', null, array("attr" => array("placeholder" => "01234567")))
            ->add('autorisation_photos', 'checkbox', array('required'=>false))
            ->add('form_saison_link', LicenseeSaisonType::class);


        if ($options["admin"])
            $builder->add('user', 'entity', array("class" => 'SLNRegisterBundle:User',
                                                  "query_builder" => function (UserRepository $er) {
                                                     return $er->createQueryBuilder('u')
                                                               ->select('u')
                                                               ->addOrderBy('u.nom', 'ASC');
                                                  },
                                                  "label" => "Rattaché au compte"))
                    ->add('fonctions', 'choice', array(
                          'label' => 'Fonctions spéciales',
                          'choices' => Licensee::getFonctionNames(),
                          'multiple' => true,
                          'expanded' => true));
    }

    /**
     * Set default options to set to the instance
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array("admin" => false,
                                     "public_groups" => array()));
    }

    /** @ignore */
    public function getName()
    {
        return 'sln_registerbundle_licenseetype';
    }
}

