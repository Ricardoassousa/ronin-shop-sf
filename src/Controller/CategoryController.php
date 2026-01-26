<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function index(EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(Category::class)->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * Creates a new category entity and handles the form submission.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView()
        ]);
    }

    /**
     * Edits an existing category entity and handles the form submission.
     *
     * @param Request $request
     * @param Category $category
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(Request $request, Category $category, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $category->setUpdatedAt(new DateTime());

            $em->flush();

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView()
        ]);
    }

    /**
     * Deletes a category entity after validating the CSRF token.
     *
     * @param Request $request
     * @param Category $category
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function delete(Request $request, Category $category, EntityManagerInterface $em): Response
    {
        $emptyCategory = count($category->getProducts()) < 1;
        if ($emptyCategory) {
            if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
                $em->remove($category);
                $em->flush();
            }
        }

        return $this->redirectToRoute('category_index');
    }

    /**
     * Displays the details of a category entity.
     *
     * @param Category
     * @return Response
     */
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category
        ]);
    }

}