<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Logger\AnalyticsLogger;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class ProductController extends AbstractController
{
    /**
     * Returns a paginated list of active products as JSON.
     *
     * This method retrieves products that are marked as active,
     * applies pagination based on query parameters `page` and `limit`,
     * converts each product to an array using the Product::toArray() method,
     * and returns the result as a JSON response.
     *
     * Query Parameters:
     * - page (int, optional): The page number to retrieve, default is 1
     * - limit (int, optional): The maximum number of products per page, default is 10, maximum is 20
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param AnalyticsLogger $analyticsLogger
     * @return JsonResponse
     */
    public function listAction(Request $request, EntityManagerInterface $em, AnalyticsLogger $analyticsLogger): JsonResponse
    {
        try {
            $page = max(1, $request->query->getInt('page', 1));
            $limit = min(20, $request->query->getInt('limit', 10));

            $products = $em->getRepository(Product::class)->findActivePaginated($page, $limit);

            $productsArray = array_map(fn($product) => $product->toArray(), $products);

            $analyticsLogger->log(
                'API products list accessed',
                [
                    'page' => $page,
                    'limit' => $limit,
                    'count' => count($productsArray),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            return $this->json([
                'status' => 'success',
                'message' => 'Products retrieved successfully',
                'data' => $productsArray
            ], 200);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Error fetching API products list',
                [
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );

            return $this->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred',
                'data' => null
            ], 500);
        }
    }

    /**
     * Returns the details of a single product as JSON.
     *
     * This method retrieves a product by its ID, converts it to an array using
     * the Product::toArray() method, and returns it in a standardized JSON response
     * with status, message, and data. Logs access and errors.
     *
     * URL Parameters:
     * - id (int, required): The unique identifier of the product
     *
     * @param int $id
     * @param EntityManagerInterface $em
     * @param AnalyticsLogger $analyticsLogger
     * @return JsonResponse
     */
    public function detailsAction(int $id, EntityManagerInterface $em, AnalyticsLogger $analyticsLogger): JsonResponse
    {
        try {
            $product = $em->getRepository(Product::class)->find($id);

            if (!$product) {
                $analyticsLogger->log(
                    'Product not found in API detail endpoint',
                    [
                        'product_id' => $id,
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::WARNING
                );

                return $this->json([
                    'status' => 'error',
                    'message' => 'Product not found',
                    'data' => null
                ], 404);
            }

            $analyticsLogger->log(
                'API product detail accessed',
                [
                    'product_id' => $id,
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            return $this->json([
                'status' => 'success',
                'message' => 'Product retrieved successfully',
                'data' => $product->toArray()
            ], 200);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Error fetching product detail in API',
                [
                    'product_id' => $id,
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );

            return $this->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred',
                'data' => null
            ], 500);
        }
    }

    /**
     * Searches active products based on query parameters and returns them as JSON.
     *
     * This endpoint allows filtering products by name, price range and pagination.
     * Results are returned using a standardized JSON structure with status, message
     * and data. All products are converted using Product::toArray().
     *
     * Query Parameters:
     * - name      (string, optional): Product name search
     * - minPrice  (float, optional): Minimum price
     * - maxPrice  (float, optional): Maximum price
     * - page      (int, optional): Page number (default 1)
     * - limit     (int, optional): Items per page (default 10, max 20)
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param AnalyticsLogger $analyticsLogger
     * @return JsonResponse
     */
    public function searchAction(Request $request, EntityManagerInterface $em, AnalyticsLogger $analyticsLogger): JsonResponse
    {
        try {
            $page  = max(1, $request->query->getInt('page', 1));
            $limit = min(20, $request->query->getInt('limit', 10));

            $name = $request->query->get('name');
            $minPrice = $request->query->get('minPrice');
            $maxPrice = $request->query->get('maxPrice');

            $searchParams = [
                'name' => $name,
                'minPrice' => $minPrice,
                'maxPrice' => $maxPrice,
            ];

            // Validate name
            if ($name != null && !is_string($name)) {
                return $this->json([
                    'status' => 'Error',
                    'message' => 'Invalid parameter: name must be a string.'
                ], 400);
            }

            // Validate minPrice
            if ($minPrice != null && !is_numeric($minPrice)) {
                return $this->json([
                    'status' => 'Error',
                    'message' => 'Invalid parameter: minPrice must be a number.'
                ], 400);
            } elseif ($minPrice !== null) {
                $searchParams['minPrice'] = (float)$minPrice;
            }

            // Validate maxPrice
            if ($maxPrice != null && !is_numeric($maxPrice)) {
                return $this->json([
                    'status' => 'Error',
                    'message' => 'Invalid parameter: maxPrice must be a number.'
                ], 400);
            } elseif ($maxPrice != null) {
                $searchParams['maxPrice'] = (float)$maxPrice;
            }

            // Validate minPrice <= maxPrice
            if (isset($searchParams['minPrice'], $searchParams['maxPrice']) && $searchParams['minPrice'] > $searchParams['maxPrice']) {
                return $this->json([
                    'status' => 'Error',
                    'message' => 'Invalid price range: minPrice cannot be greater than maxPrice.'
                ], 400);
            }

            $products = $em
                ->getRepository(Product::class)
                ->findProductByFilterQuery($searchParams, true)
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getResult();

            $productsArray = array_map(
                fn (Product $product) => $product->toArray(),
                $products
            );

            $analyticsLogger->log(
                'API product search executed',
                [
                    'filters' => $searchParams,
                    'page' => $page,
                    'limit' => $limit,
                    'count' => count($productsArray),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            return $this->json([
                'status' => 'success',
                'message' => 'Products retrieved successfully',
                'data' => $productsArray
            ], 200);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Error executing product search',
                [
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );

            return $this->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred',
                'data' => null
            ], 500);
        }
    }

}