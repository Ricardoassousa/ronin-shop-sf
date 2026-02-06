<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Form\UserProfileFormType;
use App\Logger\SecurityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Throwable;

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
     * @param SecurityLogger $securityLogger
     * @return Response
     */
    public function editProfileAction(Request $request, SecurityLogger $securityLogger): Response
    {
        try {
            $user = $this->getUser();
            $securityLogger->log(
                'Profile edit page accessed.',
                [
                    'user_id' => $user->getId(),
                    'ip' => $request->getClientIp(),
                    'user_agent' => $request->headers->get('User-Agent'),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            $cart = $this->em->getRepository(Cart::class)->findOneBy([
                'user' => $user,
                'status' => Cart::STATUS_ACTIVE
            ]);
            $cartItems = $cart ? $cart->getItems() : [];
            $form = $this->createForm(UserProfileFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $securityLogger->log(
                    'Profile edit form submitted.',
                    [
                        'user_id' => $user->getId(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::INFO
                );
            }

            if ($form->isSubmitted() && $form->isValid()) {
                if ($form->get('password')->getData()) {
                    $user->setPassword(
                        $this->passwordEncoder->encodePassword($user, $form->get('password')->getData())
                    );

                    $securityLogger->log(
                        'User password updated.',
                        [
                            'user_id' => $user->getId(),
                            'source' => [
                                'method' => __METHOD__,
                                'line' => __LINE__
                            ]
                        ],
                        LogLevel::NOTICE
                    );
                }

                $this->em->flush();

                $securityLogger->log(
                    'User profile updated successfully.',
                    [
                        'user_id' => $user->getId(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::NOTICE
                );

                $this->addFlash('success', 'Profile updated successfully!');
                return $this->redirectToRoute('app_profile');
            }

            return $this->render('profile/edit.html.twig', [
                'profileForm' => $form->createView(),
                'cart' => $cart,
                'cartItems' => $cartItems
            ]);

        } catch (Throwable $e) {
            $securityLogger->log(
                'Unexpected error during profile edit.',
                [
                    'user_id' => $this->getUser() ? $this->getUser()->getId() : null,
                    'exception' => $e
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

}