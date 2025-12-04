<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\UserRolesType;
use Symfony\Component\Form\Test\TypeTestCase;

class UserRolesTypeTest extends TypeTestCase
{
    /**
     * Test the form creation
     * This test ensures that the form is created with the expected fields.
     */
    public function testFormCreation()
    {
        $form = $this->factory->create(UserRolesType::class);

        // Ensure the form has a 'roles' field
        $this->assertTrue($form->has('roles'));
        $this->assertInstanceOf(\Symfony\Component\Form\Form::class, $form);
    }

    /**
     * Test form submission with valid data
     * This test ensures that when valid data is submitted, 
     * it correctly sets the roles on the User entity.
     */
    public function testSubmitValidData()
    {
        $formData = [
            'roles' => ['ROLE_USER', 'ROLE_ADMIN']
        ];

        // Create a new User entity to bind to the form
        $model = new User();
        $form = $this->factory->create(UserRolesType::class, $model);
        $form->submit($formData);

        // Assert the form is submitted
        $this->assertTrue($form->isSubmitted());
        // Assert the form is valid
        $this->assertTrue($form->isValid());

        // Assert that the roles have been correctly set on the User model
        $this->assertEquals($formData['roles'], $model->getRoles());
    }

    /**
     * Test form submission with no roles selected
     * This test ensures that when no roles are selected, 
     * the roles array remains empty.
     */
    public function testSubmitNoRoles()
    {
        $formData = [
            'roles' => []  // No roles selected
        ];

        $model = new User();
        $form = $this->factory->create(UserRolesType::class, $model);
        $form->submit($formData);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());

        // Assert that the roles are not empty, but contain the default 'ROLE_USER'
        $this->assertEquals(['ROLE_USER'], $model->getRoles(), 'Roles should contain the default "ROLE_USER" when no roles are selected.');
    }

    /**
     * Test form submission with invalid data
     * This test ensures that the form submission will fail if invalid roles are provided.
     */
    public function testSubmitInvalidData()
    {
        // Simulate invalid roles
        $formData = [
            'roles' => ['INVALID_ROLE']  // Invalid role
        ];

        $model = new User();
        $form = $this->factory->create(UserRolesType::class, $model);
        $form->submit($formData);

        // Assert the form is submitted
        $this->assertTrue($form->isSubmitted());
        // Assert that the form is invalid
        $this->assertFalse($form->isValid());

        // Check that the error is present in the 'roles' field
        $rolesErrors = $form->get('roles')->getErrors(true);
        $this->assertGreaterThan(0, count($rolesErrors));
    }

}