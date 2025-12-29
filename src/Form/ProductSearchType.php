<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\ProductSearch;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => '',
                'required' => false,
                'label' => 'Category'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductSearch::class,
        ]);
    }

}