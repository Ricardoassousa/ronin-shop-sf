<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductSearch;
use App\Form\ProductSearchType;
use App\Form\ProductType;
use App\Service\SlugGenerator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
     * @param EntityManagerInterface $em
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function indexAction(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request): Response
    {
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

        return $this->render('product/index.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView()
        ]);
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
     * @return Response
     */
    public function newAction(Request $request, EntityManagerInterface $em, SlugGenerator $slugGenerator): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugGenerator->generate($product->getName());
            $product->setSlug($slug);

            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
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
     * @return Response
     */
    public function editAction(Request $request, Product $product, EntityManagerInterface $em, SlugGenerator $slugGenerator): Response
    {
        $form = $this->createForm(ProductType::class, $product, [
            'is_edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $product->getSlug();
            $newSlug = $slugGenerator->generate($product->getName());

            if ($slug != $newSlug):
                $product->setSlug($newSlug);
            endif;
            $product->setUpdatedAt(new DateTime());

            $em->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    /**
     * Deletes a product entity after validating the CSRF token.
     *
     * @param Request $request
     * @param Product $product
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function deleteAction(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $em->remove($product);
            $em->flush();
        }

        return $this->redirectToRoute('product_index');
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
     * @return Response
     */
    public function showAction(Product $product, string $slug): Response
    {
        if ($product->getSlug() != $slug) {
            return $this->redirectToRoute('product_show', [
                'id' => $product->getId(),
                'slug' => $product->getSlug(),
            ], 301);
        }

        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }

}