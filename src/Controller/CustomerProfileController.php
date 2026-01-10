<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CustomerProfile;
use App\Form\CustomerProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CustomerProfileController extends AbstractController
{
    /**
     * Displays and processes the customer profile edit form.
     *
     * This method allows an authenticated user to view and update their customer profile.
     * If the user does not yet have a profile, a new one is created and associated
     * with the current user.
     *
     * The method also retrieves the user's active shopping cart (if any) and passes
     * it to the view so cart-related information can be displayed.
     *
     * If the form is submitted and valid, the profile is persisted and the user
     * is redirected back to the profile page with a success message.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     * @throws AccessDeniedException
     */
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user, 'status' => Cart::STATUS_ACTIVE]);
        $cartItems = $cart ? $cart->getItems() : [];

        if (!$user) {
            throw $this->createAccessDeniedException('You need to be logged in to access this page.');
        }

        $profile = $user->getCustomerProfile() ?? null;
        if ($profile == null) {
            $profile = new CustomerProfile();
            $profile->setUser($user);
        }

        $form = $this->createForm(CustomerProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($profile);
            $em->flush();

            $this->addFlash('success', 'Profile saved successfully!');

            return $this->redirectToRoute('app_customer_profile');
        }

        return $this->render('customer_profile/edit.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart,
            'cartItems' => $cartItems
        ]);
    }

}