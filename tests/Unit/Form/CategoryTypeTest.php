<?php

namespace App\Tests\Form;

use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Component\Form\Test\TypeTestCase;

class CategoryTypeTest extends TypeTestCase
{
    /**
     * Test form submission with valid data.
     *
     * This test verifies that:
     * - The form is synchronized after submission
     * - All submitted values are correctly mapped to the Category entity
     */
    public function testSubmitValidData()
    {
        $formData = [
            'name' => 'Technology',
            'slug' => 'technology',
            'description' => 'Category about technology',
            'isActive' => true,
        ];

        // Create a new Category entity to bind to the form
        $model = new Category();

        // Create the form
        $form = $this->factory->create(CategoryType::class, $model);

        // Submit valid form data
        $form->submit($formData);

        // Assert that the form is synchronized
        $this->assertTrue($form->isSynchronized());

        // Assert that form data is correctly mapped to the entity
        $this->assertEquals('Technology', $model->getName());
        $this->assertEquals('technology', $model->getSlug());
        $this->assertEquals('Category about technology', $model->getDescription());
        $this->assertTrue($model->isActive());
    }

    /**
     * Test form creation.
     *
     * This test checks if the Category form is created successfully
     * and contains all expected fields.
     */
    public function testFormFieldsExist()
    {
        $form = $this->factory->create(CategoryType::class);

        $this->assertTrue($form->has('name'));
        $this->assertTrue($form->has('slug'));
        $this->assertTrue($form->has('description'));
        $this->assertTrue($form->has('isActive'));
    }

    /**
     * Test optional description field.
     *
     * This test verifies that:
     * - The description field is optional
     * - Submitting the form without a description keeps the value null
     */
    public function testDescriptionIsOptional()
    {
        $formData = [
            'name' => 'News',
            'slug' => 'news',
            'isActive' => true,
        ];

        // Create a new Category entity to bind to the form
        $model = new Category();

        // Create the form
        $form = $this->factory->create(CategoryType::class, $model);

        // Submit form data without description
        $form->submit($formData);

        // Assert that the form is synchronized
        $this->assertTrue($form->isSynchronized());

        // Assert that the optional description remains null
        $this->assertNull($model->getDescription());
    }

}