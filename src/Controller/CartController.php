<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use App\Logger\CartLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

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
     * @param CartLogger $cartLogger
     * @return Response
     */
    public function showAction(CartService $cartService, CartLogger $cartLogger): Response
    {
        try {
            $cart = $cartService->getCart($this->getUser());
            $total = $cartService->getTotal($this->getUser());

            $cartLogger->log('Cart viewed', [
                'user_id' => $this->getUser()->getId(),
                'cart_items' => count($cart->getItems()),
                'source' => [
                    'method' => __METHOD__,
                    'line' => __LINE__
                ]
            ]);

            return $this->render('cart/show.html.twig', [
                'cart' => $cart,
                'total' => $cartService->getTotal($this->getUser())
            ]);

        } catch (Throwable $e) {
            $cartLogger->log('Unexpected error in showAction', [
                'exception' => $e
            ]);

            throw $e;
        }
    }

    /**
     * Add a product to the user's cart.
     *
     * This method adds the product to the cart with a default quantity of 1.
     * If the product is already in the cart, the quantity is updated.
     *
     * @param CartService $cartService
     * @param Product $product
     * @param CartLogger $cartLogger
     * @return Response
     */
    public function addAction(CartService $cartService, Product $product, CartLogger $cartLogger): Response
    {
        try {
            $cartService->addProduct($this->getUser(), $product);

            $cartLogger->log('Product added to cart', [
                'user_id' => $this->getUser()->getId(),
                'product_id' => $product->getId(),
                'product_name' => $product->getName(),
                'source' => [
                    'method' => __METHOD__,
                    'line' => __LINE__
                ]
            ]);

            $this->addFlash('success', sprintf('"%s" added to your cart.', $product->getName()));
            return $this->redirectToRoute('cart_show');

        } catch (Throwable $e) {
            $cartLogger->log('Unexpected error in addAction', [
                'exception' => $e
            ]);

            throw $e;
        }
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
     * @param CartLogger $cartLogger
     * @return Response
     */
    public function updateAction(CartService $cartService, Product $product, int $quantity, CartLogger $cartLogger): Response
    {
        try {
            $cartService->updateQuantity($this->getUser(), $product, $quantity);

            $cartLogger->log('Cart product updated', [
                'user_id' => $this->getUser()->getId(),
                'product_id' => $product->getId(),
                'quantity' => $quantity,
                'source' => [
                    'method' => __METHOD__,
                    'line' => __LINE__
                ]
            ]);

            $this->addFlash('success', 'Cart updated successfully.');
            return $this->redirectToRoute('cart_show');

        } catch (Throwable $e) {
            $cartLogger->log('Unexpected error in updateAction', [
                'exception' => $e,
                'source' => [
                    'method' => __METHOD__,
                    'line' => __LINE__
                ]
            ]);

            throw $e;
        }
    }

    /**
     * Remove a product from the user's cart.
     *
     * This method removes a product from the user's cart. If the product does not exist in the cart, an exception is thrown.
     *
     * @param CartService $cartService
     * @param Product $product
     * @param CartLogger $cartLogger
     * @return Response
     */
    public function removeAction(CartService $cartService, Product $product, CartLogger $cartLogger): Response
    {
        try {
            $cartService->removeProduct($this->getUser(), $product);

            $cartLogger->log('Product removed from cart', [
                'user_id' => $this->getUser()->getId(),
                'product_id' => $product->getId(),
                'product_name' => $product->getName(),
                'source' => [
                    'method' => __METHOD__,
                    'line' => __LINE__
                ]
            ]);

            $this->addFlash('success', sprintf('"%s" removed from your cart.', $product->getName()));
            return $this->redirectToRoute('cart_show');

        } catch (Throwable $e) {
            $cartLogger->log('Unexpected error in removeAction', [
                'exception' => $e,
                'source' => [
                    'method' => __METHOD__,
                    'line' => __LINE__
                ]
            ]);

            throw $e;
        }
    }

}