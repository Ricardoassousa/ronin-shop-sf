<?php

namespace App\Controller;

use App\Logger\SecurityLogger;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Throwable;

/**
 * Controller responsible for user authentication.
 *
 * Handles displaying the login form, processing authentication errors,
 * and logging out users via the Symfony security firewall.
 */
class SecurityController extends AbstractController
{
    /**
     * Displays the login form and handles authentication errors.
     *
     * This method is called when the user visits the login page.
     * It displays the form with the last submitted username and any authentication error.
     *
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @param SecurityLogger $securityLogger
     * @return Response
     */
    public function loginAction(Request $request, AuthenticationUtils $authenticationUtils, SecurityLogger $securityLogger): Response
    {
        try {
            $lastUsername = $authenticationUtils->getLastUsername();
            $error = $authenticationUtils->getLastAuthenticationError();

            if ($error) {
                $securityLogger->log(
                    'Authentication failed on login page.',
                    [
                        'username' => $lastUsername,
                        'exception' => $error,
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::WARNING
                );
            } else {
                $securityLogger->log(
                    'Login page accessed.',
                    [
                        'ip' => $request->getClientIp(),
                        'user_agent' => $request->headers->get('User-Agent'),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::INFO
                );
            }

            return $this->render('security/login.html.twig', [
                'last_username' => $lastUsername,
                'error' => $error
            ]);

        } catch (Throwable $e) {
            $securityLogger->log(
                'Unexpected error while rendering login page.',
                [
                    'exception' => $e
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

    /**
     * Handles user logout.
     *
     * This method is intercepted by the Symfony firewall and does not need to do any processing.
     * The firewall will automatically log out the user and redirect them to the configured route.
     *
     * @param SecurityLogger $securityLogger
     * @return void
     * @throws LogicException
     */
    public function logoutAction(SecurityLogger $securityLogger): void
    {
        try {
            $securityLogger->log(
                'Logout action triggered.',
                [
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            throw new LogicException('Intercepted by the logout firewall.');
        } catch (LogicException $e) {
            $securityLogger->log(
                'Logout intercepted by firewall.',
                [
                    'message' => $e->getMessage()
                ],
                LogLevel::DEBUG
            );

            throw $e;
        } catch (Throwable $e) {
            $securityLogger->log(
                'Unexpected error during logout.',
                [
                    'exception' => $e
                ],
                LogLevel::CRITICAL
            );

            throw $e;
        }
    }

}