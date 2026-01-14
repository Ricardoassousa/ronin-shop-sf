<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Cart;
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

        $address = new Address();

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

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

        // TODO: Fix Order 1-1 Address
        dd($cart);
        $address = $em->getRepository(Address::class)->findOneBy([
            'id' => 1
        ]);
        if ($address == null) {
            return $this->redirectToRoute('cart_show');
        }

        return $this->render('checkout/summary.html.twig', [
            'cart' => $cart,
            'address' => $address
        ]);
    }

}