<?php

namespace App\Form;

use App\Entity\ProductSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'Product Name'
            ])
            ->add('sku', TextType::class, [
                'required' => false,
                'label' => 'Stock Keeping Unit'
            ])
            ->add('minPrice', NumberType::class, [
                'required' => false,
                'label' => 'Min Price'
            ])
            ->add('maxPrice', NumberType::class, [
                'required' => false,
                'label' => 'Max Price'
            ])
            ->add('stock', IntegerType::class, [
                'required' => false,
                'label' => 'Stock'
            ])
            ->add('isActive', CheckboxType::class, [
                'required' => false,
                'label' => 'Only Active',
                'value' => true,
                'label_attr' => ['class' => 'checkbox-inline']
            ])
            ->add('startDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Start Date',
                'attr' => ['class' => 'datepicker']
            ])
            ->add('endDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'End Date',
                'attr' => ['class' => 'datepicker']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductSearch::class,
        ]);
    }

}