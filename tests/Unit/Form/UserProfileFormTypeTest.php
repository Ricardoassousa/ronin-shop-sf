<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\UserProfileFormType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Forms as FormFactory;

class UserProfileFormTypeTest extends TypeTestCase
{
    /**
     * Test the form creation.
     * This test checks if the form is created successfully and contains the expected fields.
     */
    public function testFormCreation()
    {
        $form = $this->factory->create(UserProfileFormType::class);

        // Ensure the form is an instance of Symfony\Component\Form\Form
        $this->assertInstanceOf(\Symfony\Component\Form\Form::class, $form);

        // Assert that the form has 'email' and 'password' fields
        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('password'));
    }

    /**
     * Test form submission with valid data.
     * This test checks if the form is submitted correctly and if the data is populated in the model.
     */
    public function testSubmitValidData()
    {
        $formData = [
            'email' => 'test@example.com',
            'password' => [
                'first' => 'NewPassword123',
                'second' => 'NewPassword123',
            ]
        ];

        // Create a new User object to bind to the form
        $model = new User();
        $form = $this->factory->create(UserProfileFormType::class, $model);

        // Submit the valid form data
        $form->submit($formData);

        // Assert that the form is submitted and valid
        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());

        // Assert that the form data is correctly bound to the model
        $this->assertEquals('test@example.com', $model->getEmail());

        // Assert that the password is not empty (it may be encrypted)
        $this->assertNotEmpty($model->getPassword());
    }

    /**
     * Test form submission with invalid data (password mismatch).
     * This test checks if the form validation fails when passwords don't match.
     */
    public function testSubmitInvalidData()
    {
        $formData = [
            'email' => 'test@example.com',
            'password' => [
                'first' => 'NewPassword123',
                'second' => 'DifferentPassword123',
            ]
        ];

        // Create a new User object to bind to the form
        $model = new User();
        $form = $this->factory->create(UserProfileFormType::class, $model);

        // Submit the invalid form data
        $form->submit($formData);

        // Assert that the form is submitted
        $this->assertTrue($form->isSubmitted());

        // Assert that the form is invalid (due to password mismatch)
        $this->assertFalse($form->isValid());

        // Assert that the password field has validation errors
        $passwordErrors = $form->get('password')->getErrors(true);
        $this->assertGreaterThan(0, count($passwordErrors));
    }

}