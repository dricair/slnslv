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
use SLN\RegisterBundle\Entity\Repository\LicenseeRepository;

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
        $defaultGroup = $options["defaultGroup"];
        if (!$defaultGroup) $defaultGroup = new Groupe();

        $builder
            ->add('groupe', 'entity', array(
                  'class' => 'SLNRegisterBundle:Groupe'))
            ->add('licensees', 'entity', array(
                  'class' => 'SLNRegisterBundle:Licensee',
                  'multiple' => true,
                  'expanded' => false,
                  'attr' => array("size" => 10)))
            ->add('title', 'text')
            ->add('body', 'textarea');
        
    }

    /**
     * Set default options to set to the instance
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array("defaultGroup" => null));
    }

    /** @ignore */
    public function getName()
    {
        return 'sln_registerbundle_licenseeselecttype';
    }
}


