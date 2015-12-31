<?php

namespace Eti\JobeetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Eti\JobeetBundle\Entity\Job;

class JobType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', ['choices' => Job::getTypes(), 'expanded' => true, 'constraints' => [
                new Assert\NotBlank(),
                new Assert\Choice(['callback' => ['Eti\JobeetBundle\Entity\Job', 'getTypeValues']])
            ]])
            ->add('category', null, [
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('company', 'text', [
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('file', 'file', ['label' => 'Company logo', 'required' => false, 
                'constraints' => [
                    new Assert\Image()
                ]
            ])
            ->add('url', null, [
                'constraints' => [
                    new Assert\Url()
                ]
            ])
            ->add('position', null, [
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('location', 'text', [
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('description', 'textarea', [
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('howToApply', 'text', ['label' => 'How to apply?','constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('isPublic', null, ['label' => 'Public?'])
            ->add('email', 'email', [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email()
                ]
            ])
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eti\JobeetBundle\Entity\Job'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'job';
    }
}