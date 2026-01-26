<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartAddress;
use App\Entity\OrderAddress;
use App\Entity\OrderItem;
use App\Entity\OrderShop;
use App\Service\CartService;
use App\Form\CartAddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller responsible for managing the checkout process.
 *
 * This controller handles all steps of the checkout flow for an authenticated user:
 *  - Address step (addressAction)
 *  - Order summary step (summaryAction)
 *  - Order confirmation step (confirmAction)
 *  - Checkout success step (successAction)
 *
 * Each action ensures:
 *  - The user is authenticated
 *  - There is an active cart with at least one item
 *  - Cart addresses are correctly associated
 *  - Orders and order items are persisted in the database
 */
class CheckoutController extends AbstractController
{
    /**
     * Handles the checkout address step for the current user.
     *
     * This method retrieves the active cart for the authenticated user,
     * displays the address form, and persists the submitted address.
     * After successful submission, the user is redirected to the checkout summary.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param CartService $cartService
     * @return Response
     */
    public function addressAction(Request $request, EntityManagerInterface $em, CartService $cartService): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $em->getRepository(Cart::class)->findOneBy([
            'user' => $user,
            'status' => Cart::STATUS_ACTIVE
        ]);
        if ($cart == null || count($cart->getItems()) == 0) {
            return $this->redirectToRoute('cart_show');
        }

        $cartAddress = $cart->getCartAddress();
        if ($cartAddress == null) {
            $cartAddress = new CartAddress();
        }

        $form = $this->createForm(CartAddressType::class, $cartAddress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $cartAddress->setCart($cart);
            $em->persist($cartAddress);
            $em->flush();

            return $this->redirectToRoute('checkout_summary');
        }

        return $this->render('checkout/address.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart
        ]);
    }

    /**
     * Handles the checkout summary step for the current user.
     *
     * This method retrieves the active cart for the authenticated user,
     * checks if the cart contains items, and ensures that an address is selected.
     * If no cart is found or no items are present, the user is redirected to the cart page.
     * If no address is associated with the cart, the user is redirected to the cart page.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function summaryAction(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $em->getRepository(Cart::class)->findOneBy([
            'user' => $user,
            'status' => Cart::STATUS_ACTIVE
        ]);
        if ($cart == null || count($cart->getItems()) == 0) {
            return $this->redirectToRoute('cart_show');
        }

        $cartAddress = $em->getRepository(CartAddress::class)->findOneBy([
            'cart' => $cart
        ]);
        if ($cartAddress == null) {
            return $this->redirectToRoute('cart_show');
        }

        return $this->render('checkout/summary.html.twig', [
            'cart' => $cart,
            'cartAddress' => $cartAddress
        ]);
    }

    /**
     * Handles the order confirmation step for the current user.
     *
     * This method finalizes the checkout process by creating a new order
     * from the authenticated user's active cart. It ensures that the user
     * is logged in, that an active cart exists, and that the cart contains
     * at least one item.
     *
     * An address is retrieved and associated with the order (temporary logic).
     * A new Order entity is created with a pending status, and each cart item
     * is converted into an OrderItem associated with the order.
     *
     * If the user is not authenticated, they are redirected to the login page.
     * If no active cart exists or the cart is empty, the user is redirected
     * back to the cart page.
     *
     * After successfully persisting the order and its items, the user is
     * redirected to the checkout success page.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function confirmAction(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $em->getRepository(Cart::class)->findOneBy([
            'user' => $user,
            'status' => Cart::STATUS_ACTIVE
        ]);
        if ($cart == null || count($cart->getItems()) == 0) {
            return $this->redirectToRoute('cart_show');
        }

        $cartAddress = $em->getRepository(CartAddress::class)->findOneBy([
            'cart' => $cart
        ]);
        if ($cartAddress == null) {
            return $this->redirectToRoute('cart_show');
        }

        $cart->setStatus(Cart::STATUS_ORDERED);

        $order = new OrderShop();
        $order->setUser($user);
        $order->setStatus(OrderShop::STATUS_PENDING);
        $em->persist($order);

        $orderAddress = new OrderAddress();
        $orderAddress->setOrderShop($order);
        $orderAddress->setPrimaryAddress($cartAddress->getPrimaryAddress());
        $orderAddress->setSecondaryAddress($cartAddress->getSecondaryAddress());
        $orderAddress->setCity($cartAddress->getCity());
        $orderAddress->setState($cartAddress->getState());
        $orderAddress->setPostalCode($cartAddress->getPostalCode());
        $orderAddress->setCountry($cartAddress->getCountry());
        $em->persist($orderAddress);

        foreach ($cart->getItems() as $cartItem) {

            $product = $cartItem->getProduct();
            if ($product->getStock() < $cartItem->getQuantity()) {
                return $this->redirectToRoute('cart_show');
            }
            $product->setStock($product->getStock() - $cartItem->getQuantity());

            $item = new OrderItem();
            $item->setOrderShop($order);
            $item->setProduct($product);
            $item->setUnitPrice($product->getPrice());
            $item->setSubtotal($item->getUnitPrice() * $item->getQuantity());
            $item->setQuantity($cartItem->getQuantity());
            $em->persist($item);
        }
        $em->flush();

        return $this->redirectToRoute('checkout_success');
    }

    /**
     * Handles the checkout success step for the current user.
     *
     * This method validates the final stage of the checkout flow by ensuring
     * that the user is authenticated and has an active cart with at least
     * one item. If these conditions are not met, the user is redirected
     * to the appropriate page.
     *
     * The user's active cart is retrieved and temporarily associated with
     * a fixed address (placeholder logic to be improved). The cart and
     * address are then passed to the checkout success view for display.
     *
     * If the user is not authenticated, they are redirected to the login page.
     * If no active cart exists or the cart is empty, the user is redirected
     * back to the cart page.
     *
     * @return Response
     */
    public function successAction(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('checkout/success.html.twig');
    }

}