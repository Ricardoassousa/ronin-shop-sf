<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CustomerProfile;
use App\Form\CustomerProfileType;
use App\Logger\SecurityLogger;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Throwable;

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
     * @param SecurityLogger $securityLogger
     * @return Response
     * @throws AccessDeniedException
     */
    public function editAction(Request $request, EntityManagerInterface $em, SecurityLogger $securityLogger): Response
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                throw new AccessDeniedException('User must be logged in.');
            }

            $securityLogger->log(
                'Customer profile page accessed.',
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

            $cart = $em->getRepository(Cart::class)->findOneBy([
                'user' => $user,
                'status' => Cart::STATUS_ACTIVE
            ]);
            $cartItems = $cart ? $cart->getItems() : [];

            $profile = $user->getCustomerProfile() ?? new CustomerProfile();
            $profile->setUser($user);

            $form = $this->createForm(CustomerProfileType::class, $profile);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $securityLogger->log(
                    'Customer profile form submitted.',
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
                $isNewProfile = $profile->getId() == null;

                $profile->setUpdatedAt(new Datetime());
                $em->persist($profile);
                $em->flush();

                $securityLogger->log(
                    $isNewProfile ? 'New customer profile created.' : 'Customer profile updated.',
                    [
                        'user_id' => $user->getId(),
                        'profile_id' => $profile->getId(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::NOTICE
                );

                $this->addFlash('success', 'Profile saved successfully!');
                return $this->redirectToRoute('app_customer_profile');
            }

            return $this->render('customer_profile/edit.html.twig', [
                'form' => $form->createView(),
                'cart' => $cart,
                'cartItems' => $cartItems
            ]);

        } catch (Throwable $e) {
            $securityLogger->log(
                'Unexpected error during customer profile edit.',
                [
                    'user_id' => $this->getUser() ? $this->getUser()->getId() : null,
                    'exception' => $e,
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

    /**
     * Retrieve the list of states for a given country via AJAX.
     *
     * This method returns a JSON response containing the states/provinces
     * associated with the provided country code. If the country code
     * is not found, an empty array is returned.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getStatesAjaxAction(Request $request): JsonResponse
    {
        $country = $request->query->get('country');

        $states = [
            'US' => [
                ['code' => 'AL', 'name' => 'Alabama'],
                ['code' => 'CA', 'name' => 'California'],
                ['code' => 'NY', 'name' => 'New York'],
            ],
            'CA' => [
                ['code' => 'AB', 'name' => 'Alberta'],
                ['code' => 'ON', 'name' => 'Ontario'],
                ['code' => 'QC', 'name' => 'Quebec'],
            ],
            'BR' => [
                ['code' => 'SP', 'name' => 'São Paulo'],
                ['code' => 'RJ', 'name' => 'Rio de Janeiro'],
                ['code' => 'MG', 'name' => 'Minas Gerais'],
            ],
        ];

        return new JsonResponse($states[$country] ?? []);
    }

}