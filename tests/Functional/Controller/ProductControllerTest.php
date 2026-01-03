<?php

namespace App\Tests\Controller;

use App\Entity\Product;
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

    /**
     * Test product index page contains the search form.
     */
    public function testIndexPageContainsSearchForm(): void
    {
        $client = static::createClient();

        $user = $this->createTestUser('ROLE_ADMIN');
        $client->loginUser($user);

        $crawler = $client->request('GET', '/product/');

        $this->assertGreaterThan(
            0,
            $crawler->filter('form[name="product_search"]')->count(),
            'Search not found.'
        );

        $this->assertGreaterThan(0, $crawler->filter('input[name*="[name]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name*="[sku]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name*="[minPrice]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name*="[maxPrice]"]')->count());
    }

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

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="product_search"]');
    }

    // /**
    //  * Test index page with valid search filters.
    //  */
    // public function testIndexWithSearchFilters(): void
    // {
    //     $client = static::createClient();

    //     $user = $this->createTestUser();
    //     $client->loginUser($user);

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
    //     $client->followRedirects(true);

    //     $user = $this->createTestUser('ROLE_ADMIN');
    //     $client->loginUser($user);

    //     $this->createTestProducts(15);

    //     $crawler = $client->request('GET', '/product?page=2');

    //     $this->assertResponseIsSuccessful();
    //     $this->assertResponseStatusCodeSame(200);

    //     $this->assertSelectorExists('.product-item');
    // }

    /**
     * Test index page with empty form submission.
     *
     * Ensures default date logic does not break the request.
     */
    public function testEmptyFormSubmission(): void
    {
        $client = static::createClient();

        $user = $this->createTestUser('ROLE_ADMIN');
        $client->loginUser($user);

        $crawler = $client->request('GET', '/product/');

        $form = $crawler->filter('form')->form([]);

        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * Creates a test user and persists it to the database.
     *
     * This method creates a new user with a unique email, a hashed password,
     * and assigns the role of 'ROLE_ADMIN'. The user is then persisted to
     * the database using Doctrine's EntityManager.
     *
     * @return User The created User entity.
     */
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

    /**
     * Creates a set of test products and persists them to the database.
     *
     * This method generates a specified number of test products, deletes any
     * existing products in the database first, and then persists the newly
     * created products to the database using Doctrine's EntityManager.
     * Each product will have a unique slug, SKU, and short description.
     *
     * @param int $count The number of products to create. Default is 15.
     */
    private function createTestProducts(int $count = 15): void
    {
        $em = static::getContainer()->get('doctrine')->getManager();

        $em->createQuery('DELETE FROM App\Entity\Product p')->execute();

        for ($i = 1; $i <= $count; $i++) {
            $uniqueId = uniqid();

            $product = new Product();
            $product->setName("Product $i");
            // $product->setSlug('product-' . $uniqueId);
            $product->setSku('SKU-' . $uniqueId);
            $product->setShortDescription("Short description for product $i");
            $product->setDescription("Description for product $i");
            $product->setPrice(mt_rand(100, 1000));
            $product->setStock(mt_rand(1, 50));
            $product->setIsActive(true);

            $em->persist($product);
        }

        $em->flush();
    }

}