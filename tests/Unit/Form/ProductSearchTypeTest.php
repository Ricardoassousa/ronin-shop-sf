<?php

namespace App\Tests\Form;

use App\Entity\ProductSearch;
use App\Form\ProductSearchType;
use DateTime;
use Symfony\Component\Form\Test\TypeTestCase;

class ProductSearchTypeTest extends TypeTestCase
{
    /**
     * Test form submission with valid data.
     *
     * This test verifies that:
     * - The form is synchronized after submission
     * - All submitted values are correctly mapped to the ProductSearch model
     * - Date fields are properly transformed from string to DateTime objects
     */
    public function testSubmitValidData(): void
    {
        $formData = [
            'name' => 'Notebook',
            'sku' => 'NB-123',
            'minPrice' => 1000.50,
            'maxPrice' => 5000.99,
            'stock' => 10,
            'isActive' => true,
            'startDate' => '2024-01-01',
            'endDate' => '2024-12-31',
        ];

        // Create a new ProductSearch object to bind to the form
        $model = new ProductSearch();

        // Create the form
        $form = $this->factory->create(ProductSearchType::class, $model);

        // Submit valid form data
        $form->submit($formData);

        // Assert that the form is synchronized
        $this->assertTrue($form->isSynchronized());

        // Assert that form data is correctly mapped to the model
        $this->assertSame('Notebook', $model->getName());
        $this->assertSame('NB-123', $model->getSku());
        $this->assertSame(1000.50, $model->getMinPrice());
        $this->assertSame(5000.99, $model->getMaxPrice());
        $this->assertSame(10, $model->getStock());
        $this->assertTrue($model->isActive());

        // Assert that date fields are properly converted to DateTime objects
        $this->assertEquals(new DateTime('2024-01-01'), $model->getStartDate());
        $this->assertEquals(new DateTime('2024-12-31'), $model->getEndDate());
    }

    /**
     * Test form creation.
     *
     * This test checks if the ProductSearch form is created successfully
     * and contains all expected fields.
     */
    public function testFormHasExpectedFields(): void
    {
        $form = $this->factory->create(ProductSearchType::class);

        $this->assertTrue($form->has('name'));
        $this->assertTrue($form->has('sku'));
        $this->assertTrue($form->has('minPrice'));
        $this->assertTrue($form->has('maxPrice'));
        $this->assertTrue($form->has('stock'));
        $this->assertTrue($form->has('isActive'));
        $this->assertTrue($form->has('startDate'));
        $this->assertTrue($form->has('endDate'));
    }

    /**
     * Test form submission with empty data.
     *
     * This test verifies that:
     * - The form can be submitted with no data
     * - Optional fields remain null
     * - Checkbox fields default to false when not submitted
     */
    public function testEmptyFormSubmission(): void
    {
        $form = $this->factory->create(ProductSearchType::class);

        // Submit an empty form
        $form->submit([]);

        // Assert that the form is synchronized
        $this->assertTrue($form->isSynchronized());

        $data = $form->getData();

        // Assert that the form data is an instance of ProductSearch
        $this->assertInstanceOf(ProductSearch::class, $data);

        // Assert default values for empty submission
        $this->assertNull($data->getName());
        $this->assertNull($data->getSku());
        $this->assertNull($data->getMinPrice());
        $this->assertNull($data->getMaxPrice());
        $this->assertNull($data->getStock());

        // CheckboxType defaults to false when not submitted
        $this->assertFalse($data->isActive());

        $this->assertNull($data->getStartDate());
        $this->assertNull($data->getEndDate());
    }

}