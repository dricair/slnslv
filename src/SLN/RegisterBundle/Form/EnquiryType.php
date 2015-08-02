<?php
/**
 * Create a form for an Enquiry type
 */

namespace SLN\RegisterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use SLN\RegisterBundle\Entity\Enquiry;

/**
 * Create a form for an Enquiry type
 */
class EnquiryType extends AbstractType
{
   /**
    * Build the form
    *
    * @param FormBuilderInterface $builder
    * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', null, array('label' => 'Nom'));
        $builder->add('email', 'email', array('label' => 'Prénom'));
        $builder->add('subject', null, array('label' => 'Sujet'));
        $builder->add('body', 'textarea', array('label' => 'Message'));
    }

    /** @ignore */
    public function getName()
    {
        return 'contact';
    }
}
