<?php

namespace IRecruitBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

use IRecruitBundle\Entity\Affiliate;

class AffiliateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', null, ['constraints' => [
                    new Assert\Url()
                ]
            ])
            ->add('email', null, ['constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email()
                ]
            ])
            ->add('categories', null, ['expanded' => true])
        ;
    }
 
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IRecruitBundle\Entity\Affiliate',
        ));
    }
 
    public function getName()
    {
        return 'affiliate';
    }
}