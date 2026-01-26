<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Form\UserProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Controller responsible for managing the authenticated user's profile.
 *
 * This includes editing user information, updating passwords,
 * and optionally displaying cart-related information for context.
 */
class ProfileController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string;
     */
    private $passwordEncoder;

    /**
     * Constructor to inject the necessary services for entity management and password encoding.
     *
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;
    }

    /**
     * Allows the authenticated user to edit their profile, including updating the password.
     *
     * @param Request $request
     * @return Response
     */
    public function editProfile(Request $request): Response
    {
        $user = $this->getUser();
        $cart = $this->em->getRepository(Cart::class)->findOneBy(['user' => $user, 'status' => Cart::STATUS_ACTIVE]);
        $cartItems = $cart ? $cart->getItems() : [];
        $form = $this->createForm(UserProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData()) {
                $user->setPassword(
                    $this->passwordEncoder->encodePassword($user, $form->get('password')->getData())
                );
            }
            $this->em->flush();
            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'profileForm' => $form->createView(),
            'cart' => $cart,
            'cartItems' => $cartItems
        ]);
    }

}