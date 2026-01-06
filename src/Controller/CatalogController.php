<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductSearch;
use App\Entity\User;
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
        $user = $this->getUser();
        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user, 'status' => Cart::STATUS_ACTIVE]);
        $cartItems = $cart ? $cart->getItems() : [];

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
            'searchForm' => $searchForm->createView(),
            'cart' => $cart,
            'cartItems' => $cartItems
        ]);
    }

    /**
     * Displays the details of a Product entity.
     *
     * This method expects both the Product entity (via ParamConverter)
     * and the slug from the URL. If the slug in the URL does not match
     * the product's current slug, a 301 redirect is performed.
     *
     * @param Product $product The product entity
     * @param string $slug The slug from the URL
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

        return $this->render('catalog/show.html.twig', [
            'product' => $product
        ]);
    }

}