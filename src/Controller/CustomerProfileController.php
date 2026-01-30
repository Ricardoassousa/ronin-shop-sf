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

/**
 * Controller responsible for managing the customer's profile.
 *
 * This controller allows authenticated users to:
 *  - View and edit their customer profile
 *  - Create a profile if one does not yet exist
 *
 * The controller also integrates the user's active shopping cart
 * so that cart-related information can be displayed alongside the profile.
 *
 * Access is restricted to authenticated users.
 */
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
    public function editAction(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user, 'status' => Cart::STATUS_ACTIVE]);
        $cartItems = $cart ? $cart->getItems() : [];
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