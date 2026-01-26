<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller responsible for managing the shopping cart.
 *
 * This controller handles all cart-related actions for a user, including:
 *  - Displaying the current cart
 *  - Adding products to the cart
 *  - Updating product quantities in the cart
 *  - Removing products from the cart
 *
 * All actions ensure the cart is tied to the currently authenticated user.
 */
class CartController extends AbstractController
{
    /**
     * Display the user's shopping cart.
     *
     * @param CartService $cartService
     * @return Response
     */
    public function showAction(CartService $cartService): Response
    {
        $cart = $cartService->getCart($this->getUser());

        return $this->render('cart/show.html.twig', [
            'cart' => $cart,
            'total' => $cartService->getTotal($this->getUser())
        ]);
    }

    /**
     * Add a product to the user's cart.
     *
     * This method adds the product to the cart with a default quantity of 1.
     * If the product is already in the cart, the quantity is updated.
     *
     * @param CartService $cartService
     * @param Product $product
     * @return Response
     */
    public function addAction(CartService $cartService, Product $product): Response
    {
        $cartService->addProduct($this->getUser(), $product);
        return $this->redirectToRoute('cart_show');
    }

    /**
     * Update the quantity of a product in the cart.
     *
     * This method updates the quantity of a product in the user's cart.
     * If the quantity is less than 1, the product is removed from the cart.
     *
     * @param CartService $cartService
     * @param Product $product
     * @param int $quantity
     * @return Response
     */
    public function updateAction(CartService $cartService, Product $product, int $quantity): Response
    {
        $cartService->updateQuantity($this->getUser(), $product, $quantity);
        return $this->redirectToRoute('cart_show');
    }

    /**
     * Remove a product from the user's cart.
     *
     * This method removes a product from the user's cart. If the product does not exist in the cart, an exception is thrown.
     *
     * @param CartService $cartService
     * @param Product $product
     * @return Response
     */
    public function removeAction(CartService $cartService, Product $product): Response
    {
        $cartService->removeProduct($this->getUser(), $product);
        return $this->redirectToRoute('cart_show');
    }

}