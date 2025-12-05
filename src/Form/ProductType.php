<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Defines the form used to create and edit products.
 */
class ProductType extends AbstractType
{
    /**
     * Builds the product form.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isEdit = $options['is_edit'] ?? false;

        $builder
            ->add('name', TextType::class, [
                'label' => 'Product Name',
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
            ])
            ->add('sku', TextType::class, [
                'label' => 'SKU',
            ])
            ->add('shortDescription', TextType::class, [
                'label' => 'Short Description'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description'
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Price',
                'currency' => 'USD'
            ])
            ->add('discountPrice', MoneyType::class, [
                'label' => 'Discount Price',
                'currency' => 'USD',
                'required' => false
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock Quantity'
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Is Active'
            ])
            ->add('image', FileType::class, [
                'label' => 'Product Image',
                'mapped' => false,
                'required' => !$isEdit,
            ])
        ;
    }

    /**
     * Configures options for this form.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'is_edit' => false
        ]);
    }

}