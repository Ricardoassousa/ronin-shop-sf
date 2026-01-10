<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * Displays the login form and handles authentication errors.
     *
     * This method is called when the user visits the login page.
     * It displays the form with the last submitted username and any authentication error.
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * Handles user logout.
     *
     * This method is intercepted by the Symfony firewall and does not need to do any processing.
     * The firewall will automatically log out the user and redirect them to the configured route.
     *
     * @return void
     * @throws LogicException
     */
    public function logout(): void
    {
        throw new LogicException('Intercepted by the logout firewall.');
    }

}