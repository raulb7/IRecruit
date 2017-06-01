<?php

namespace IRecruitBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

use IRecruitBundle\Entity\Job;

class CompanyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', [
                'label' => 'E-mail',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
                'mapped'=> false,
                'data' => $options['email']
            ])
            ->add('country', 'text', [
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('city', 'text', [
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('phone', 'text', [
                'constraints' => [
                    new Assert\NotBlank(),
                    ]
            ])
            ->add('address', 'text', [
                'constraints' => [
                    new Assert\NotBlank(),
                    ]
            ])
            ->add('logo', 'file', [
                'constraints' => [
                    new Assert\Image(),
                ],
                'mapped' => false,
                'required' => false
            ])
            ->add('description', 'textarea', [
            ])
            ->add('facebook', 'text', [
                'constraints' => [
                    new Assert\Url(),
                ]
            ])
            ->add('linkedin', 'text', [
                'constraints' => [
                    new Assert\Url(),
                ]
            ])

        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['email']);
        $resolver->setDefaults(array(
            'data_class' => 'IRecruitBundle\Entity\CProfile'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'companyType';
    }
}
