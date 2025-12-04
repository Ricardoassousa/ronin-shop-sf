<?php

namespace App\Form;

use App\Entity\CustomerProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $states = [
            'US' => [
                'Alabama' => 'AL',
                'California' => 'CA',
                'New York' => 'NY'
            ],
            'CA' => [
                'Alberta' => 'AB',
                'Ontario' => 'ON',
                'Quebec' => 'QC'
            ],
            'BR' => [
                'São Paulo' => 'SP',
                'Rio de Janeiro' => 'RJ',
                'Minas Gerais' => 'MG'
            ]
        ];

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First name',
            ])
            ->add('surname', TextType::class, [
                'label' => 'Surname',
            ])
            ->add('phone', TextType::class, [
                'label' => 'Phone number'
            ])
            ->add('countryPrefixCode', TextType::class, [
                'label' => 'Country Prefix Code (+1, +55...)'
            ])
            ->add('primaryAddress', TextType::class, [
                'label' => 'Primary address'
            ])
            ->add('secondaryAddress', TextType::class, [
                'label' => 'Secondary address'
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
            ])
            ->add('country', CountryType::class, [
                'label' => 'Country',
                'preferred_choices' => ['US']
            ])
            ->add('state', ChoiceType::class, [
                'label' => 'State',
                'choices' => $states,
                'required' => false
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Postal Code'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomerProfile::class,
        ]);
    }

}