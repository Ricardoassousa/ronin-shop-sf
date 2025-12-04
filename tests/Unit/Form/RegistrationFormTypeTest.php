<?php

use Symfony\Component\Form\Test\TypeTestCase;
use App\Form\RegistrationFormType;
use App\Entity\User;

class RegistrationFormTypeTest extends TypeTestCase
{
    /**
     * Ensures that submitting valid data populates the User entity correctly
     * and that the form is submitted, synchronized, and valid.
     */
    public function testSubmitValidData()
    {
        $formData = [
            'email' => 'test@example.com',
            'password' => [
                'first' => 'Password123',
                'second' => 'Password123'
            ]
        ];

        $model = new User();
        $form = $this->factory->create(RegistrationFormType::class, $model);

        // Assert that all expected fields exist in the form
        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('password'));

        $form->submit($formData);

        // Assert correct form lifecycle
        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());

        // Assert that form data was mapped to the User entity
        $this->assertEquals('test@example.com', $model->getEmail());
    }

    /**
     * Ensures that the form becomes invalid when password and repeated password
     * fields do not match, and that validation errors are attached to the
     * password field as expected.
     */
    public function testPasswordMismatch()
    {
        $formData = [
            'email' => 'test@example.com',
            'password' => [
                'first' => 'abc123',
                'second' => 'wrong'
            ]
        ];

        $form = $this->factory->create(RegistrationFormType::class);
        $form->submit($formData);

        // The form should be invalid due to mismatched passwords
        $this->assertFalse($form->isValid());

        // The error is attached to the "password" RepeatedType field
        $passwordErrors = $form->get('password')->getErrors(true);
        $this->assertGreaterThan(0, count($passwordErrors));
    }

}