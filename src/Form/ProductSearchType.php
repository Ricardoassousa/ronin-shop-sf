<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\ProductSearch;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('shortDescription', TextType::class, [
                'required' => false,
                'label' => 'Product Short Description'
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
            ->add('availability', ChoiceType::class, [
                'required' => false,
                'placeholder' => '',
                'choices' => [
                    'In Stock' => 'in_stock',
                    'Out of Stock' => 'out_stock',
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('onSale', CheckboxType::class, [
                'required' => false,
                'label' => 'On Sale Only'
            ])
            ->add('sort', ChoiceType::class, [
                'required' => false,
                'placeholder' => 'Sort by...',
                'choices' => [
                    'Price: Low to High' => 'price_asc',
                    'Price: High to Low' => 'price_desc',
                    'Name: A to Z' => 'name_asc',
                    'Name: Z to A' => 'name_desc',
                    'Newest' => 'newest',
                    'Biggest Discount' => 'discount_desc',
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
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