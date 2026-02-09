<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Logger\AnalyticsLogger;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Controller responsible for managing product categories (CRUD).
 *
 * This controller handles actions such as:
 *  - Listing all categories
 *  - Viewing a single category
 *  - Creating new categories
 *  - Editing existing categories
 *  - Deleting categories (with CSRF protection)
 *
 * All actions ensure proper handling of forms and persistence with Doctrine.
 */
class CategoryController extends AbstractController
{
    /**
     * Displays a list of all categories.
     *
     * @param EntityManagerInterface $em
     * @param AnalyticsLogger $analyticsLogger
     * @return Response
     */
    public function indexAction(EntityManagerInterface $em, AnalyticsLogger $analyticsLogger): Response
    {
        try {
            $categories = $em->getRepository(Category::class)->findAll();

            $analyticsLogger->log('Category list accessed', [
                'count' => count($categories),
                'source' => [
                    'method' => __METHOD__,
                    'line' => __LINE__
                ],
                LogLevel::NOTICE
            ]);

            return $this->render('category/index.html.twig', [
                'categories' => $categories
            ]);

        } catch (Throwable $e) {
            $analyticsLogger->log('Unexpected error in category index', [
                'exception' => $e,
                'source' => [
                    'method' => __METHOD__,
                    'line' => __LINE__
                ],
                LogLevel::ERROR
            ]);

            throw $e;
        }
    }

    /**
     * Creates a new category entity and handles the form submission.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param AnalyticsLogger $analyticsLogger
     * @return Response
     */
    public function newAction(Request $request, EntityManagerInterface $em, AnalyticsLogger $analyticsLogger): Response
    {
        try {
            $category = new Category();
            $form = $this->createForm(CategoryType::class, $category);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($category);
                $em->flush();

                $analyticsLogger->log(
                    'New category created',
                    [
                        'category_id' => $category->getId(),
                        'name' => $category->getName(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::NOTICE
                );

                $this->addFlash('success', sprintf('Category "%s" created successfully.', $category->getName()));
                return $this->redirectToRoute('category_index');
            }

            return $this->render('category/new.html.twig', [
                'category' => $category,
                'form' => $form->createView()
            ]);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Unexpected error in category creation',
                [
                    'exception' => $e
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

    /**
     * Edits an existing category entity and handles the form submission.
     *
     * @param Request $request
     * @param Category $category
     * @param EntityManagerInterface $em
     * @param AnalyticsLogger $analyticsLogger
     * @return Response
     */
    public function editAction(Request $request, Category $category, EntityManagerInterface $em, AnalyticsLogger $analyticsLogger): Response
    {
        try {
            $form = $this->createForm(CategoryType::class, $category);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $category->setUpdatedAt(new DateTime());
                $em->flush();

                $analyticsLogger->log(
                    'Category edited',
                    [
                        'category_id' => $category->getId(),
                        'name' => $category->getName(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::NOTICE
                );

                $this->addFlash('success', sprintf('Category "%s" updated successfully.', $category->getName()));
                return $this->redirectToRoute('category_index');
            }

            return $this->render('category/edit.html.twig', [
                'category' => $category,
                'form' => $form->createView()
            ]);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Unexpected error in category edit',
                [
                    'exception' => $e
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

    /**
     * Deletes a category entity after validating the CSRF token.
     *
     * @param Request $request
     * @param Category $category
     * @param EntityManagerInterface $em
     * @param AnalyticsLogger $analyticsLogger
     * @return Response
     */
    public function deleteAction(Request $request, Category $category, EntityManagerInterface $em, AnalyticsLogger $analyticsLogger): Response
    {
        try {
            $emptyCategory = count($category->getProducts()) < 1;
            if (!$emptyCategory) {
                $this->addFlash('warning', 'Cannot delete category with associated products.');
                return $this->redirectToRoute('category_index');
            }

            if ($emptyCategory && $this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
                $em->remove($category);
                $em->flush();

                $analyticsLogger->log(
                    'Category deleted',
                    [
                        'category_id' => $category->getId(),
                        'name' => $category->getName(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ],
                    ],
                    LogLevel::WARNING
                );
            }

            $this->addFlash('success', sprintf('Category "%s" deleted successfully.', $category->getName()));
            return $this->redirectToRoute('category_index');

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Unexpected error in category delete',
                [
                    'exception' => $e
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

    /**
     * Displays the details of a category entity.
     *
     * @param Category
     * @return Response
     */
    public function showAction(Category $category, AnalyticsLogger $analyticsLogger): Response
    {
        try {
            $analyticsLogger->log(
                'Category viewed',
                [
                    'category_id' => $category->getId(),
                    'name' => $category->getName(),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            return $this->render('category/show.html.twig', [
                'category' => $category
            ]);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Unexpected error in category show',
                [
                    'exception' => $e
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

}