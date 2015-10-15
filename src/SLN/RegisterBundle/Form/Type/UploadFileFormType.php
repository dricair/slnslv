<?php
/**
 * Create a form for a file upload
 */

namespace SLN\RegisterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use SLN\RegisterBundle\Entity\UploadFile;


/**
 * Form to upload a file
 */
class UploadFileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('filename')
                ->add('inline');
    }

    /**
     * Set default options to set to the instance
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'SLN\RegisterBundle\Entity\UploadFile',
        ));
    }

    /** @ignore */
    public function getName()
    {
        return 'sln_registerbundle_licenseemail';
    }
}

