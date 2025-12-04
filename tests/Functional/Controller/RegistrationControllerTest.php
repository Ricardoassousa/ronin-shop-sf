<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;

class RegistrationControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $testEmail;

    /**
     * Prepare test environment.
     * - Create a client
     * - Get the entity manager
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Test user profile editing
     * - Submit profile form (edit profile)
     * - Assert redirection to profile page
     * - Assert user information is updated in the database
     */
public function testUserCanEditProfile(): void
{
    // Crie um usuário com um e-mail único para o teste
    $this->testEmail = 'test' . uniqid() . '@example.com'; // E-mail único a cada execução do teste
    $user = $this->createUser();

    // Faça login com o usuário criado
    $this->client->loginUser($user);

    // Acesse a página de edição de perfil
    $crawler = $this->client->request('GET', '/profile');

    // Assegure que o formulário esteja visível
    $this->assertResponseIsSuccessful();
    $this->assertSelectorExists('form');
    $this->assertSelectorExists('input[name="user_profile_form[email]"]');
    $this->assertSelectorExists('input[name="user_profile_form[password][first]"]');
    $this->assertSelectorExists('input[name="user_profile_form[password][second]"]');

    // Submeta o formulário de edição com dados atualizados
    $form = $crawler->selectButton('Save')->form([
        'user_profile_form[email]' => 'updated_' . $this->testEmail,
        'user_profile_form[password][first]' => 'UpdatedPassword123',
        'user_profile_form[password][second]' => 'UpdatedPassword123',
    ]);

    $this->client->submit($form);

    // Verifique se o redirecionamento ocorreu para a página de perfil
    $this->assertResponseRedirects('/profile');
    $this->client->followRedirect();

    // Verifique se o usuário foi atualizado no banco de dados
    $updatedUser = $this->entityManager
        ->getRepository(User::class)
        ->findOneBy(['email' => 'updated_' . $this->testEmail]);

    $this->assertNotNull($updatedUser, 'User should be updated in the database.');
    $this->assertEquals('updated_' . $this->testEmail, $updatedUser->getEmail());
}

    /**
     * Helper function to create a user for testing
     * 
     * @return User
     */
    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('test' . uniqid() . '@example.com');
        $user->setPassword('Password123'); // Simulate an already encoded password

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * Clean up after test
     * - Remove only the user created in this test
     */
    protected function tearDown(): void
    {
        if ($this->testEmail) {
            $user = $this->entityManager
                ->getRepository(User::class)
                ->findOneBy(['email' => $this->testEmail]);

            if ($user) {
                $this->entityManager->remove($user);
                $this->entityManager->flush();
            }
        }

        parent::tearDown();

        $this->entityManager = null;
        $this->client = null;
    }

}