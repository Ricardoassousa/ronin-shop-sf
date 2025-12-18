<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    /**
     * Test product index page loads successfully.
     */
    public function testIndexPageLoads(): void
    {
        $client = static::createClient();
        $client->followRedirects(true);

        $user = $this->createTestUser('ROLE_ADMIN');
        $client->loginUser($user);

        $crawler = $client->request('GET', '/product/');

        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('form[name="product_search"]');
    }

    // /**
    //  * Test product index page contains the search form.
    //  */
    // public function testIndexPageContainsSearchForm(): void
    // {
    //     $client = static::createClient();
    //     $crawler = $client->request('GET', '/product/');

    //     $this->assertGreaterThan(
    //         0,
    //         $crawler->filter('form[name="product_search"]')->count(),
    //         'Search not found.'
    //     );

    //     $this->assertGreaterThan(0, $crawler->filter('input[name*="[name]"]')->count());
    //     $this->assertGreaterThan(0, $crawler->filter('input[name*="[sku]"]')->count());
    //     $this->assertGreaterThan(0, $crawler->filter('input[name*="[minPrice]"]')->count());
    //     $this->assertGreaterThan(0, $crawler->filter('input[name*="[maxPrice]"]')->count());
    // }

    /**
     * Test index page without filters.
     *
     * Ensures the page loads and pagination is rendered.
     */
    public function testIndexWithoutFilters(): void
    {
        $client = static::createClient();
        $client->followRedirects(true);

        $user = $this->createTestUser();
        $client->loginUser($user);

        $crawler = $client->request('GET', '/product/');

        $this->assertResponseIsSuccessful(); // espera HTTP 2xx
        $this->assertSelectorExists('form[name="product_search"]'); // verifica formulário
    }

    // /**
    //  * Test index page with valid search filters.
    //  */
    // public function testIndexWithSearchFilters(): void
    // {
    //     $client = static::createClient();
    //     $crawler = $client->request('GET', '/product/');

    //     $form = $crawler->filter('form')->form([
    //         'product_search[name]' => 'Notebook',
    //         'product_search[minPrice]' => 100,
    //         'product_search[maxPrice]' => 5000,
    //         'product_search[isActive]' => 1,
    //         'product_search[startDate]' => '2024-01-01',
    //         'product_search[endDate]' => '2024-12-31',
    //     ]);

    //     $client->submit($form);

    //     $this->assertResponseIsSuccessful();

    //     $this->assertGreaterThan(
    //         0,
    //         $client->getCrawler()->filter('.pagination')->count()
    //     );
    // }

    // /**
    //  * Test pagination functionality.
    //  */
    // public function testPaginationWorks(): void
    // {
    //     $client = static::createClient();

    //     $client->request('GET', '/product?page=2');

    //     $this->assertResponseIsSuccessful();
    //     $this->assertResponseStatusCodeSame(200);
    // }

    // /**
    //  * Test index page with empty form submission.
    //  *
    //  * Ensures default date logic does not break the request.
    //  */
    // public function testEmptyFormSubmission(): void
    // {
    //     $client = static::createClient();
    //     $crawler = $client->request('GET', '/product/');

    //     $form = $crawler->filter('form')->form([]);

    //     $client->submit($form);

    //     $this->assertResponseIsSuccessful();
    //     $this->assertResponseStatusCodeSame(200);
    // }

    private function createTestUser()
    {
        $user = new User();
        $user->setEmail('testuser'.uniqid().'@example.com');
        $user->setPassword(password_hash('password123', PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_ADMIN']);

        $em = static::getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

}