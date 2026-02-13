<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Vich\UploaderBundle\Form\Type\VichImageType;

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
            ->add('discountPrice', IntegerType::class, [
                'label' => 'Discount (%)',
                'required' => false,
                'attr' => [
                    'min' => 1,
                    'max' => 99,
                    'step' => 1
                ],
                'data' => isset($options['data']) && $options['data']->getDiscountPrice() != null
                ? $options['data']->getDiscountPrice() * 100
                : null,
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock Quantity'
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Is Active'
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Product Image',
                'required' => !$isEdit,
                'allow_delete' => false,
                'download_uri' => false
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => '',
                'required' => false,
                'label' => 'Category'
            ]);
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