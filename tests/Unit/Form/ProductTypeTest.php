<?php

namespace App\Tests\Form;

use App\Entity\Product;
use App\Form\ProductType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProductTypeTest extends TypeTestCase
{
    protected function getExtensions()
    {
        $vichImageTypeMock = $this->createMock(VichImageType::class);
        return [
            $vichImageTypeMock
        ];
    }

    // public function testSubmitValidData()
    // {
    //     $formData = [
    //         'name' => 'Test Product',
    //         'slug' => 'test-product',
    //         'sku' => 'SKU12345',
    //         'shortDescription' => 'A short description',
    //         'description' => 'A detailed description of the product',
    //         'price' => 100.00,
    //         'discountPrice' => 80.00,
    //         'stock' => 10,
    //         'isActive' => true,
    //         'imageFile' => null, // Ignorando a imagem
    //     ];

    //     $product = new Product();

    //     $form = $this->factory->create(ProductType::class, $product);

    //     $form->submit($formData);

    //     $this->assertTrue($form->isSynchronized());

    //     $this->assertEquals('Test Product', $product->getName());
    //     $this->assertEquals('test-product', $product->getSlug());
    //     $this->assertEquals('SKU12345', $product->getSku());
    //     $this->assertEquals('A short description', $product->getShortDescription());
    //     $this->assertEquals('A detailed description of the product', $product->getDescription());
    //     $this->assertEquals(100.00, $product->getPrice());
    //     $this->assertEquals(80.00, $product->getDiscountPrice());
    //     $this->assertEquals(10, $product->getStock());
    //     $this->assertTrue($product->getIsActive());
    //     $this->assertNull($product->getImageFile());
    // }

}