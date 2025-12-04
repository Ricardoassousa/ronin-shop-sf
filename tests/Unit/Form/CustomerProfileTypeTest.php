<?php

namespace App\Tests\Form;

use App\Entity\CustomerProfile;
use App\Form\CustomerProfileType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Forms;

class CustomerProfileTypeTest extends TypeTestCase
{
    /**
     * Test the form creation.
     * - Ensure that all necessary fields exist in the form.
     */
    public function testFormCreation()
    {
        $form = $this->factory->create(CustomerProfileType::class);

        // Check if the form is an instance of Symfony\Component\Form\Form
        $this->assertInstanceOf(\Symfony\Component\Form\Form::class, $form);
        
        // Check if all required fields are present in the form
        $this->assertInstanceOf(\Symfony\Component\Form\Form::class, $form);
        $this->assertTrue($form->has('firstName'));
        $this->assertTrue($form->has('surname'));
        $this->assertTrue($form->has('phone'));
        $this->assertTrue($form->has('countryPrefixCode'));
        $this->assertTrue($form->has('primaryAddress'));
        $this->assertTrue($form->has('secondaryAddress'));
        $this->assertTrue($form->has('city'));
        $this->assertTrue($form->has('country'));
        $this->assertTrue($form->has('state'));
        $this->assertTrue($form->has('postalCode'));
    }

    /**
     * Test submitting valid data to the form.
     * - Submit correct data
     * - Assert form submission is successful
     * - Verify that the model is populated with the correct data
     */
    public function testSubmitValidData()
    {
        $formData = [
            'firstName' => 'John',
            'surname' => 'Doe',
            'phone' => '1234567890',
            'countryPrefixCode' => '+1',
            'primaryAddress' => '123 Main St',
            'secondaryAddress' => 'Apt 4B',
            'city' => 'New York',
            'country' => 'US',
            'state' => 'CA',  // California
            'postalCode' => '10001',
        ];

        $model = new CustomerProfile();
        $form = $this->factory->create(CustomerProfileType::class, $model);

        $form->submit($formData);

        // Assert that the form was submitted and is valid
        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());

        // Assert that the model's fields match the submitted data
        $this->assertEquals('John', $model->getFirstName());
        $this->assertEquals('Doe', $model->getSurname());
        $this->assertEquals('1234567890', $model->getPhone());
        $this->assertEquals('+1', $model->getCountryPrefixCode());
        $this->assertEquals('123 Main St', $model->getPrimaryAddress());
        $this->assertEquals('Apt 4B', $model->getSecondaryAddress());
        $this->assertEquals('New York', $model->getCity());
        $this->assertEquals('US', $model->getCountry());
        $this->assertEquals('CA', $model->getState());  // California
        $this->assertEquals('10001', $model->getPostalCode());
    }

    // /**
    //  * Test submitting invalid data (no state when country requires it).
    //  * - Submit data with missing state (required for US)
    //  * - Assert that the form is invalid
    //  * - Ensure that a validation error is triggered on the 'state' field
    //  */
    // public function testSubmitInvalidDataNoState()
    // {
    //     $formData = [
    //         'firstName' => 'John',
    //         'surname' => 'Doe',
    //         'phone' => '1234567890',
    //         'countryPrefixCode' => '+1',
    //         'primaryAddress' => '123 Main St',
    //         'secondaryAddress' => 'Apt 4B',
    //         'city' => 'New York',
    //         'country' => 'US',
    //         'state' => '',  // No state provided
    //         'postalCode' => '10001',
    //     ];

    //     $model = new CustomerProfile();
    //     $form = $this->factory->create(CustomerProfileType::class, $model);

    //     $form->submit($formData);

    //     // Assert the form was submitted
    //     $this->assertTrue($form->isSubmitted());
    //     // Assert the form is invalid due to missing state
    //     $this->assertFalse($form->isValid());

    //     // Ensure that an error is present for the 'state' field
    //     $this->assertArrayHasKey('state', $form->getErrors(true)->getForm()->all());
    // }

    /**
     * Test submitting a valid data without the state field for a country where it's not required (e.g., Brazil).
     */
    public function testSubmitValidDataNoStateBR()
    {
        $formData = [
            'firstName' => 'Jane',
            'surname' => 'Doe',
            'phone' => '9876543210',
            'countryPrefixCode' => '+55',
            'primaryAddress' => '456 Secondary St',
            'secondaryAddress' => 'Apt 10A',
            'city' => 'São Paulo',
            'country' => 'BR',
            'state' => '',  // State is not required for Brazil
            'postalCode' => '05000-000',
        ];

        $model = new CustomerProfile();
        $form = $this->factory->create(CustomerProfileType::class, $model);

        $form->submit($formData);

        // Assert the form was submitted and is valid
        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());

        // Assert that the model's fields match the submitted data
        $this->assertEquals('Jane', $model->getFirstName());
        $this->assertEquals('Doe', $model->getSurname());
        $this->assertEquals('9876543210', $model->getPhone());
        $this->assertEquals('+55', $model->getCountryPrefixCode());
        $this->assertEquals('456 Secondary St', $model->getPrimaryAddress());
        $this->assertEquals('Apt 10A', $model->getSecondaryAddress());
        $this->assertEquals('São Paulo', $model->getCity());
        $this->assertEquals('BR', $model->getCountry());
        $this->assertEquals('', $model->getState());  // State can be empty for Brazil
        $this->assertEquals('05000-000', $model->getPostalCode());
    }

}