<?php
/**
 * Create a form to select a list of licensees
 */

namespace SLN\RegisterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Licensee;

/**
 * Form to select a list of licensees
 */
class LicenseeSelectType extends AbstractType
{
   /**
    * Build the form
    *
    * @param FormBuilderInterface $builder
    * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $em = $options["em"];
        $defaultGroup = $options["defaultGroup"];

        $groupChoices = array();
        foreach ($em->getRepository('SLNRegisterBundle:Groupe')->findAll() as $group) {
            $groupChoices[$group->getId()] = $group->getNom();
        }

        $licenseeChoices = array();
        if ($defaultGroup)
          foreach($em->getRepository('SLNRegisterBundle:Licensee')->getAllForGroupe($defaultGroup) as $licensee)
            $licenseeChoices[$licensee->getId()] = $licensee->getNom() . " " . $licensee->getPrenom();

        $builder
            ->add('groupe', 'choice', array(
                    'choices' => $groupChoices))
            ->add('licensees', 'choice', array(
                    'choices' => $licenseeChoices,
                    'multiple' => true,
                    'expanded' => false,
                    'attr' => array("size" => 10)));
        
    }

    /**
     * Set default options to set to the instance
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array("em" => null,
                                     "defaultGroup" => null));
    }

    /** @ignore */
    public function getName()
    {
        return 'sln_registerbundle_licenseeselecttype';
    }
}


