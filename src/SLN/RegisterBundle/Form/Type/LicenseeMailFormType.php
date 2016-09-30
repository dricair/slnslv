<?php
/**
 * Create a form to select a list of licensees
 */

namespace SLN\RegisterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Horaire;
use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\Repository\LicenseeRepository;
use SLN\RegisterBundle\Entity\Repository\GroupeRepository;
use SLN\RegisterBundle\Form\Type\UploadFileFormType;

/**
 * Form to select a list of licensees
 */
class LicenseeMailFormType extends AbstractType
{
   /**
    * Build the form
    *
    * @param FormBuilderInterface $builder
    * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $defaultGroup = $options["defaultGroup"];
        $em = $options["em"];
        if (!$defaultGroup) $defaultGroup = new Groupe();

        $groupChoices = [];

        // List of groups
        foreach ($em->getRepository('SLNRegisterBundle:Groupe')->findAll() as $groupe) {
            $category = $groupe->getCategorieName();
            if (!array_key_exists($category, $groupChoices)) $groupChoices[$category] = [];
            $groupChoices[$category][$groupe->getId()] = $groupe->getNom();

            if ($groupe->getMultiple()) {
                $jours = Horaire::getJours();
                foreach($groupe->multipleList() as $jour) {
                    $groupChoices[$category][sprintf("%s.%s", $groupe->getId(), $jour)] = sprintf("%s du %s", $groupe->getNom(), $jours[$jour]);
                }
            }
        }

        // Competition categories
        $competition = "Catégories de compétition";
        foreach(array_keys(Groupe::competitionCategories()) as $index => $name) {
          $groupChoices[$competition][Licensee::COMPETITION_OFFSET + $index] = $name;
        }

        // Special functions
        $special = "Fonctions spéciales";
        foreach (Licensee::getFonctionNames() as $index => $fonction) {

            if ($index == Licensee::OFFICIEL) {
                foreach(array_keys(Groupe::competitionCategories()) as $cidx => $cname) {
                    $groupChoices[$special][sprintf("%s.%s", Licensee::FONCTIONS_OFFSET + $index, $cidx)] = "$fonction $cname";
                }
            }
            
            else
              $groupChoices[$special][Licensee::FONCTIONS_OFFSET + $index] = $fonction;
        }

        $builder
            ->add('groupe', 'choice', array(
                  'choices' => $groupChoices))
            ->add('licensees', 'entity', array(
                  'class' => 'SLNRegisterBundle:Licensee',
                  'multiple' => true,
                  'expanded' => false,
                  'attr' => array("size" => 10)))
            ->add('title', 'text')
            ->add('body', 'textarea')
            ->add('files', 'collection', array('type' => new UploadFileFormType(),
                                               'allow_add' => true,
                                               'allow_delete' => true));
        
    }

    /**
     * Set default options to set to the instance
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array("data_class" => 'SLN\RegisterBundle\Entity\LicenseeMail',
                                     "defaultGroup" => null, 
                                     "em" => null));
    }

    /** @ignore */
    public function getName()
    {
        return 'sln_registerbundle_licenseemail';
    }
}



