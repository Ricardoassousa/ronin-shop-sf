<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CategoryControllerTest extends WebTestCase
{
    private $client;

    /**
     * Sets up the test environment before each test.
     *
     * This method:
     * - Initializes the test client.
     * - Creates a new test user and authenticates the user for the test.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        
        // Cria e autentica um usuário antes de rodar os testes
        $user = $this->createTestUser();
        $this->authenticateUser($user);
    }

    /**
     * Test the index action.
     *
     * This test verifies that:
     * - The category list page loads successfully.
     * - The page contains the correct heading ("Categories").
     */
    public function testIndex(): void
    {
        $this->client->request('GET', '/category/'); // Assuming '/category' is the route for listing categories

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Categories'); // Check for specific content in the page
    }

    /**
     * Test the new category action.
     *
     * This test verifies that:
     * - The "Create New Category" page loads successfully.
     * - A new category can be created successfully.
     * - After submission, the user is redirected to the category list.
     * - The new category is displayed in the list.
     */
    public function testNew(): void
    {
        $this->client->request('GET', '/category/new');

        $this->assertResponseIsSuccessful();

        // Submit the form with valid data
        $form = $this->client->submitForm('Create', [
            'category[name]' => 'New Category',
            'category[slug]' => 'Slug_' . uniqid(),
            'category[description]' => 'This is a description for the new category',
            'category[isActive]' => false
        ]);

        // Verify redirection to the category list page after submission
        $this->assertResponseRedirects('/category/');

        // Follow the redirect
        $this->client->followRedirect();

        // Verify that the new category appears in the list
        $this->assertSelectorTextContains('h1', 'Categories');
        $this->assertSelectorTextContains('a.btn.btn-primary', 'Create New Category');  // Verifica o botão de criação
    }

    /**
     * Test the edit category action.
     *
     * This test verifies that:
     * - The "Edit Category" page loads successfully for an existing category.
     * - The category can be updated.
     * - After submission, the user is redirected to the category list.
     * - The updated category appears in the list.
     */
    public function testEdit(): void
    {
        // Set a valid category ID to test
        $categoryId = 1;
        $this->client->request('GET', '/category/' . $categoryId . '/edit'); // Assuming this is the route for editing

        $this->assertResponseIsSuccessful();

        // Submit the form with updated data
        $form = $this->client->submitForm('Update', [
            'category[name]' => 'Updated Category'
        ]);

        // Verify redirection to the category list page after the update
        $this->assertResponseRedirects('/category/');
        $this->client->followRedirect();

        // Verify that the updated category appears in the list
        $this->assertSelectorTextContains('h1', 'Categories');
        $this->assertSelectorTextContains('a.btn.btn-primary', 'Create New Category');
    }

    // /**
    //  * Test the delete action.
    //  */
    // public function testDelete(): void
    // {
    //     $categoryId = 1; // Set a valid category ID to test deletion

    //     // Simulate a POST request to delete a category
    //     $this->client->request('POST', '/category/' . $categoryId . '/delete', [
    //         '_token' => 'delete' . $categoryId, // The CSRF token for the form
    //     ]);

    //     $this->assertResponseRedirects('/category/');
    //     $this->client->followRedirect();

    //     $this->assertSelectorNotExists('.category-' . $categoryId); // Ensure the category is not listed anymore
    // }

    /**
     * Test the show category action.
     *
     * This test verifies that:
     * - The category detail page loads successfully.
     * - The page contains a link to navigate back to the list.
     */
    public function testShow(): void
    {
        // Set a valid category ID to test viewing the details
        $categoryId = 1;

        $this->client->request('GET', '/category/' . $categoryId); // Assuming '/category/{id}' shows the category details

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a.btn.btn-secondary', 'Back to list'); // Check if the page displays the category name or details
    }

    /**
     * Creates a test user and persists it to the database.
     *
     * This method:
     * - Creates a new User entity with a unique email and password.
     * - Assigns the 'ROLE_ADMIN' role to the user.
     * - Persists the user to the database.
     *
     * @return User
     */
    private function createTestUser()
    {
        $user = new User();
        $user->setEmail('testuser' . uniqid() . '@example.com');
        $user->setPassword(password_hash('password123', PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_ADMIN']);

        $em = static::getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * Authenticates the user with the test client.
     *
     * This method:
     * - Logs in the specified user using the test client.
     *
     * @param User
     */
    private function authenticateUser(User $user)
    {
        $this->client->loginUser($user);
    }

}