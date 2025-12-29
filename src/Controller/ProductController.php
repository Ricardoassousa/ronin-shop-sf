<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductSearch;
use App\Form\ProductSearchType;
use App\Form\ProductType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Controller used to manage products (CRUD).
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
    public function index(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request): Response
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
     * Creates a new product entity and handles the form submission.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

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
    * Edits an existing product entity and handles the form submission.
    *
    * @param Request $request
    * @param Product $product
    * @param EntityManagerInterface $em
    * @return Response
    */
    public function edit(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProductType::class, $product, [
            'is_edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

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
    public function delete(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $em->remove($product);
            $em->flush();
        }

        return $this->redirectToRoute('product_index');
    }

    /**
    * Displays the details of a product entity.
    *
    * @param Product
    * @return Response
    */
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }

}