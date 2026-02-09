<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\ProductSearch;
use App\Form\ProductSearchType;
use App\Form\ProductType;
use App\Logger\AnalyticsLogger;
use App\Logger\StockLogger;
use App\Service\SlugGenerator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Throwable;

/**
 * Controller responsible for managing products (CRUD).
 *
 * This includes listing products, creating, editing, deleting,
 * and displaying product details. The controller also handles
 * search filters, pagination, and slug-based redirections.
 */
class ProductController extends AbstractController
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
            $searchParams = array();

            if ($searchForm->isSubmitted() && $searchForm->isValid()) {

                $name = $searchForm['name']->getData();
                $sku = $searchForm['sku']->getData();
                $minPrice = $searchForm['minPrice']->getData();
                $maxPrice = $searchForm['maxPrice']->getData();
                $stock = $searchForm['stock']->getData();
                $category = $searchForm['category']->getData();
                $startDate = $searchForm['startDate']->getData();
                $endDate = $searchForm['endDate']->getData();


                if (!empty($name)) {
                    $searchParams['name'] = $name;
                }

                if (!empty($sku)) {
                    $searchParams['sku'] = $sku;
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

                if (!empty($startDate)) {
                    $searchParams['startDate'] = $startDate;
                } else {
                    $startDate = new DateTime();
                    $startDate->setTime(0, 0, 0);
                    $startDate->setDate(2000, 1, 1);
                    $startDate->format('yyyy-mm-dd');

                    $searchParams['startDate'] = $startDate;
                }

                if (!empty($endDate)) {
                    $searchParams['endDate'] = $endDate->setTime(23, 59, 59);
                } else {
                    $endDate = new DateTime();
                    $endDate->setTime(23, 59, 59);
                    $endDate->setDate(date('Y'), date('m'), date('d'));
                    $endDate->format('yyyy-mm-dd');
                    $searchParams['endDate'] = $endDate;
                }
            }

            $query = $em->getRepository(Product::class)->findProductByFilterQuery($searchParams);

            $pagination = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                10
            );

            $analyticsLogger->log(
                'Products list accessed',
                [
                    'search_params' => $searchParams,
                    'page' => $request->query->getInt('page', 1),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            return $this->render('product/index.html.twig', [
                'pagination' => $pagination,
                'searchForm' => $searchForm->createView()
            ]);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Error rendering product list',
                [
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );

            throw($e);
        }
    }

    /**
     * Creates a new Product entity and handles the form submission.
     *
     * This method processes the request, validates the form,
     * generates a unique slug for the product using SlugGenerator,
     * persists the entity to the database, and redirects as needed.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param SlugGenerator $slugGenerator
     * @param AnalyticsLogger $analyticsLogger
     * @return Response
     */
    public function newAction(Request $request, EntityManagerInterface $em, SlugGenerator $slugGenerator, AnalyticsLogger $analyticsLogger): Response
    {
        try {
            $product = new Product();
            $form = $this->createForm(ProductType::class, $product);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $slug = $slugGenerator->generate($product->getName());
                $product->setSlug($slug);
                $em->persist($product);
                $em->flush();

                $analyticsLogger->log(
                    'Product created',
                    [
                        'product_id' => $product->getId(),
                        'name' => $product->getName(),
                        'slug' => $slug,
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::NOTICE
                );

                $this->addFlash('success', 'Product created successfully.');
                return $this->redirectToRoute('product_index');
            }

            return $this->render('product/new.html.twig', [
                'product' => $product,
                'form' => $form->createView()
            ]);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Error creating product',
                [
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

    /**
     * Edits an existing Product entity and handles the form submission.
     *
     * This method processes the request, validates the form,
     * updates the product's data, regenerates the slug if the name changes
     * using the SlugGenerator service, and persists the changes to the database.
     * If the slug changes, the show action should handle redirects via 301.
     *
     * @param Request $request
     * @param Product $product
     * @param EntityManagerInterface $em
     * @param SlugGenerator $slugGenerator
     * @param AnalyticsLogger $analyticsLogger
     * @param StockLogger $stockLogger
     * @return Response
     */
    public function editAction(Request $request, Product $product, EntityManagerInterface $em, SlugGenerator $slugGenerator, AnalyticsLogger $analyticsLogger, StockLogger $stockLogger): Response
    {
        try {
            $form = $this->createForm(ProductType::class, $product, ['is_edit' => true]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $oldSlug = $product->getSlug();
                $newSlug = $slugGenerator->generate($product->getName());

                if ($oldSlug != $newSlug) {
                    $product->setSlug($newSlug);
                }

                $product->setUpdatedAt(new DateTime());
                $em->flush();

                $analyticsLogger->log(
                    'Product updated',
                    [
                        'product_id' => $product->getId(),
                        'old_slug' => $oldSlug,
                        'new_slug' => $newSlug,
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::NOTICE
                );

                $stockLogger->log(
                    'Stock updated for product',
                    [
                        'product_id' => $product->getId(),
                        'new_stock' => $product->getStock(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::INFO
                );

                $this->addFlash('success', 'Product updated successfully.');
                return $this->redirectToRoute('product_index');
            }

            return $this->render('product/edit.html.twig', [
                'product' => $product,
                'form' => $form->createView()
            ]);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Error editing product',
                [
                    'product_id' => $product->getId(),
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

    /**
     * Deletes a product entity after validating the CSRF token.
     *
     * @param Request $request
     * @param Product $product
     * @param EntityManagerInterface $em
     * @param AnalyticsLogger $analyticsLogger
     * @param StockLogger $stockLogger
     * @return Response
     */
    public function deleteAction(Request $request, Product $product, EntityManagerInterface $em, AnalyticsLogger $analyticsLogger, StockLogger $stockLogger): Response
    {
        try {
            if (!$this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
                $this->addFlash('danger', 'Invalid CSRF token.');
                return $this->redirectToRoute('product_index');
            }

            $associatedOrderItem = $em->getRepository(OrderItem::class)->findOneBy(['product' => $product]);
            if ($associatedOrderItem) {
                $analyticsLogger->log(
                    'Attempted to delete product associated with an order',
                    [
                        'product_id' => $product->getId(),
                        'order_item_id' => $associatedOrderItem->getId(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::WARNING
                );

                $this->addFlash('danger', 'Cannot delete product. It is associated with existing orders.');
                return $this->redirectToRoute('product_index');
            }

            $em->remove($product);
            $em->flush();

            $analyticsLogger->log(
                'Product deleted',
                [
                    'product_id' => $product->getId(),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::WARNING
            );

            $stockLogger->log(
                'Stock removed for deleted product',
                [
                    'product_id' => $product->getId(),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

        $this->addFlash('success', 'Product deleted successfully.');
        return $this->redirectToRoute('product_index');

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Error deleting product',
                [
                    'product_id' => $product->getId(),
                    'exception' => $e->getMessage()
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
            if ($product->getSlug() != $slug) {
                $analyticsLogger->log(
                    'Product slug mismatch, redirecting',
                    [
                        'product_id' => $product->getId(),
                        'requested_slug' => $slug,
                        'actual_slug' => $product->getSlug(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::NOTICE
                );

                return $this->redirectToRoute('product_show', [
                    'id' => $product->getId(),
                    'slug' => $product->getSlug()
                ], 301);
            }

            $analyticsLogger->log(
                'Product detail viewed',
                [
                    'product_id' => $product->getId(),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            return $this->render('product/show.html.twig', [
                'product' => $product
            ]);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Error displaying product',
                [
                    'product_id' => $product->getId(),
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

}