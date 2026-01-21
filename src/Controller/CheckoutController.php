<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartAddress;
use App\Entity\OrderAddress;
use App\Entity\OrderItem;
use App\Entity\OrderShop;
use App\Service\CartService;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function addressAction(Request $request, EntityManagerInterface $em, CartService $cartService)
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

        $address = new CartAddress();

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $address->setCart($cart);
            $em->persist($address);
            $em->flush();

            $request->getSession()->set('checkout_address_id', $address->getId());

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
    public function summaryAction(Request $request, EntityManagerInterface $em)
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

        $address = $em->getRepository(OrderAddress::class)->findOneBy([
            'id' => 2
        ]);
        if ($address == null) {
            return $this->redirectToRoute('cart_show');
        }

        return $this->render('checkout/summary.html.twig', [
            'cart' => $cart,
            'address' => $address
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
    public function confirmAction(Request $request, EntityManagerInterface $em)
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

        $address = $em->getRepository(OrderAddress::class)->findOneBy([
            'id' => 1
        ]);
        if ($address == null) {
            return $this->redirectToRoute('cart_show');
        }

        $order = new Order();
        $order->setUser(user);
        $order->setAddress($address);
        $order->setStatus(Order::STATUS_PENDING);
        $em->persist($order);

        foreach ($cart->getItems() as $cartItem) {
            $item = new OrderItem();
            $item->setOrder($order);
            $item->setProduct($cartItem->getProduct());
            $item->setUnitPrice($cartItem->getProduct()->getPrice());
            $item->setQuantity($cartItem->getQuantity());
            $item->setSubtotal($item->getUnitPrice() * $item->getQuantity());
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
     * @param Request $request The current HTTP request.
     * @param EntityManagerInterface $em The Doctrine entity manager.
     * @return Response A rendered checkout success page or a redirect response.
     */
    public function successAction(Request $request, EntityManagerInterface $em)
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

        $address = $em->getRepository(OrderAddress::class)->findOneBy([
            'id' => 1
        ]);
        if ($address == null) {
            return $this->redirectToRoute('cart_show');
        }

        return $this->render('checkout/success.html.twig', [
            'cart' => $cart,
            'address' => $address
        ]);
    }

}