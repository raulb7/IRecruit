<?php

namespace Eti\JobeetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Eti\JobeetBundle\Entity\Job;

class RegisterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options['userType'] == 1)
        {
            $builder
                ->add('email', 'email', [
                    'label' => 'E-mail',
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Email(),
                    ]
                ])
                ->add('username', 'text', [
                    'label' => 'Username',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ])
                ->add('password', 'password', [
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
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
                ->add('firstName', 'text', [
                    'constraints' => [
                        new Assert\NotBlank(),
                        ]
                ])
                ->add('lastName', 'text', [
                    'constraints' => [
                        new Assert\NotBlank(),
                        ]
                ])
                ->add('birthDate', 'date', [
                    'constraints' => [
                        new Assert\NotBlank(),
                        ]
                ])
                ->add('phone', 'text', [
                    'constraints' => [
                        new Assert\NotBlank(),
                        ]
                ])
                ->add('gender', 'text', [
                    'constraints' => [
                        new Assert\NotBlank(),
                        ]
                ])
                ->add('homeAddress', 'text', [
                    'constraints' => [
                        new Assert\NotBlank(),
                        ]
                ])
                ->add('picture', 'file', [
                    'constraints' => [
                        new Assert\Image(),
                        new Assert\NotBlank(),
                    ]
                ])
                ->add('curriculumVitae', 'file', [
                    'constraints' => [
                        new Assert\File(array(
                            'maxSize' => '30M',
                            'mimeTypes' => array(
                                'application/pdf',
                                'application/x-pdf',
                            ))),
                        new Assert\NotBlank(),
                    ]
                ])
            ;
        }
        else
        {
            $builder
                ->add('email2', 'email', [
                    'label' => 'E-mail',
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Email(),
                    ]
                ])
                ->add('username2', 'text', [
                    'label' => 'Username',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ])
                ->add('password2', 'password', [
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ])
                ->add('country2', 'text', [
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ])
                ->add('city2', 'text', [
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ])
                ->add('companyName', 'text', [
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ])
                ->add('domain', 'text', [
                    'label' => 'Business / domain',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ])
                ->add('headquarterAddress', 'text', [
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ])

                ->add('companyLogo', 'file', [
                    'constraints' => [
                        new Assert\Image(),
                        new Assert\NotNull(),
                    ]
                ])
                ->add('companyUrl', 'text', [
                    'constraints' => [
                    ]
                ])
            ;
        }

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'userType',
        ));
        $resolver->setDefaults(array(
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'regType';
    }
}
