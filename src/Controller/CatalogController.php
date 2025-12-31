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
 * Controller used to manage a catalog of products.
 */
class CatalogController extends AbstractController
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
            'searchForm' => $searchForm->createView()
        ]);
    }

}