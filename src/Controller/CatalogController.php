<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductSearch;
use App\Logger\AnalyticsLogger;
use App\Form\ProductSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Controller responsible for managing the product catalog.
 *
 * This controller handles catalog-related actions, including:
 *  - Listing all products with optional search and filters
 *  - Displaying detailed information for a single product
 *
 * It also integrates the user's active cart and items for context in the views.
 */
class CatalogController extends AbstractController
{
    /**
     * Displays a list of all products.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param PaginatorInterface $paginator
     * @param AnalyticsLogger $analyticsLogger
     * @return Response
     */
    public function indexAction(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator, AnalyticsLogger $analyticsLogger): Response
    {
        try {
            $productSearch = new ProductSearch();
            $searchForm = $this->createForm(ProductSearchType::class, $productSearch);
            $searchForm->handleRequest($request);
            $searchParams = [];
            $user = $this->getUser();
            $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user, 'status' => Cart::STATUS_ACTIVE]);
            $cartItems = $cart ? $cart->getItems() : [];

            $analyticsLogger->log(
                'Catalog page accessed',
                [
                    'user_id' => $user ? $user->getId() : null,
                    'ip' => $request->getClientIp(),
                    'cart_id' => $cart ? $cart->getId() : null,
                    'cart_items' => count($cartItems),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            if ($searchForm->isSubmitted() && $searchForm->isValid()) {

                $analyticsLogger->log(
                    'Catalog search performed',
                    [
                        'user_id' => $user ? $user->getId() : null,
                        'search_criteria' => [
                            'name' => $searchForm['name']->getData(),
                            'sku' => $searchForm['sku']->getData(),
                            'category' => $searchForm['category']->getData() ? $searchForm['category']->getData()->getId() : null,
                        ],
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::INFO
                );

                $name = $searchForm['name']->getData();
                $sku = $searchForm['sku']->getData();
                $shortDescription = $searchForm['shortDescription']->getData();
                $minPrice = $searchForm['minPrice']->getData();
                $maxPrice = $searchForm['maxPrice']->getData();
                $stock = $searchForm['stock']->getData();
                $category = $searchForm['category']->getData();

                if (!empty($name)) {
                    $searchParams['name'] = $name;
                }

                if (!empty($sku)) {
                    $searchParams['sku'] = $sku;
                }

                if (!empty($shortDescription)) {
                    $searchParams['shortDescription'] = $shortDescription;
                }

                if (isset($minPrice)) {
                    $searchParams['minPrice'] = $minPrice;
                }

                if (isset($maxPrice)) {
                    $searchParams['maxPrice'] = $maxPrice;
                }

                if (isset($stock)) {
                    $searchParams['stock'] = $stock;
                }

                if ($category instanceof Category) {
                    $searchParams['categoryId'] = $category->getId();
                }
            }

            $query = $em->getRepository(Product::class)->findProductByFilterQuery($searchParams, true);

            $pagination = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                10
            );

            return $this->render('catalog/index.html.twig', [
                'pagination' => $pagination,
                'searchForm' => $searchForm ? $searchForm->createView() : null,
                'cart' => $cart,
                'cartItems' => $cartItems
            ]);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Unexpected error in CatalogController indexAction',
                [
                    'exception' => $e
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

    /**
     * Displays the details of a Product entity.
     *
     * This method expects both the Product entity (via ParamConverter)
     * and the slug from the URL. If the slug in the URL does not match
     * the product's current slug, a 301 redirect is performed.
     *
     * @param Product $product
     * @param string $slug
     * @param AnalyticsLogger $analyticsLogger
     * @return Response
     */
    public function showAction(Product $product, string $slug, AnalyticsLogger $analyticsLogger): Response
    {
        try {
            $analyticsLogger->log(
                'Product detail viewed',
                [
                    'product_id' => $product->getId(),
                    'slug' => $product->getSlug(),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            if ($product->getSlug() != $slug) {
                $this->addFlash('info', 'The product URL was updated to the latest version.');
                return $this->redirectToRoute('catalog_show', [
                    'id' => $product->getId(),
                    'slug' => $product->getSlug(),
                ], 301);
            }

            return $this->render('catalog/show.html.twig', [
                'product' => $product
            ]);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Unexpected error in CatalogController showAction',
                [
                    'exception' => $e,
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

}