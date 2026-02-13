<?php

namespace App\Controller;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Logger\SecurityLogger;
use App\Service\EmailNotificationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Throwable;

class ResetPasswordController extends AbstractController
{
    /**
     * Handles a password recovery request.
     *
     * This action receives the user's email address, checks if a user exists,
     * generates a password reset token with an expiration time and persists it
     * in the database. For security reasons, the response is always the same,
     * regardless of whether the user exists or not.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param EmailNotificationService $emailNotificationService
     * @param SecurityLogger $securityLogger
     * @return Response
     */
    public function forgotPasswordAction(Request $request, EntityManagerInterface $em, EmailNotificationService $emailNotificationService, SecurityLogger $securityLogger): Response
    {
        try {
            $securityLogger->log(
                'Forgot Password page accessed.',
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

            $form = $this->createForm(ForgotPasswordType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $email = $data['email'];

                $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

                if ($user) {
                    $token = bin2hex(random_bytes(32));

                    $resetToken = new PasswordResetToken();
                    $resetToken->setToken($token);
                    $resetToken->setUser($user);
                    $resetToken->setExpiresAt(new DateTime('+1 hour'));

                    $em->persist($resetToken);
                    $em->flush();

                    $emailNotificationService->sendPasswordResetEmail($user, $resetToken);

                    $securityLogger->log(
                        'Password reset token generated successfully.',
                        [
                            'user_id' => $user->getId(),
                            'email' => $user->getEmail(),
                            'token' => $token,
                            'source' => [
                                'method' => __METHOD__,
                                'line' => __LINE__
                            ]
                        ],
                        LogLevel::NOTICE
                    );
                } else {
                    $securityLogger->log(
                        'Password reset requested for non-existing email.',
                        [
                            'email' => $email,
                            'source' => [
                                'method' => __METHOD__,
                                'line' => __LINE__
                            ]
                        ],
                        LogLevel::WARNING
                    );
                }

                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/forgot_password.html.twig', [
                'form' => $form->createView(),
            ]);

        } catch (Throwable $e) {
            $securityLogger->log(
                'Unexpected error during password reset request.',
                [
                    'exception' => $e
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

    /**
     * Handles the password reset process using a reset token.
     *
     * This action validates the provided password reset token, ensures it has not
     * expired, and allows the user to set a new password. Once the password is
     * successfully updated, the reset token is removed to prevent reuse.
     *
     * If the token is invalid or expired, a 404 exception is thrown.
     *
     * @param Request $request
     * @param string $token
     * @param EntityManagerInterface $em
     * @param UserPasswordHasherInterface $passwordHasher
     * @param SecurityLogger $securityLogger
     * @return Response
     * @throws NotFoundHttpException
     */
    public function resetPasswordAction(Request $request, string $token, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, SecurityLogger $securityLogger)
    {
        try {
            $securityLogger->log(
                'Password reset page accessed.',
                [
                    'ip' => $request->getClientIp(),
                    'user_agent' => $request->headers->get('User-Agent'),
                    'token' => $token,
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            $resetToken = $em->getRepository(PasswordResetToken::class)->findOneBy(['token' => $token]);

            if (!$resetToken || $resetToken->getExpiresAt() < new Datetime()) {
                $securityLogger->log(
                    'Invalid or expired password reset token used.',
                    [
                        'token' => $token,
                        'ip' => $request->getClientIp(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::WARNING
                );

                throw $this->createNotFoundException();
            }

            $form = $this->createForm(ResetPasswordType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user = $resetToken->getUser();
                $password = $form->get('password')->getData();

                $hashedPassword = $passwordHasher->hashPassword($user, $password);

                $user->setPassword($hashedPassword);

                $em->flush();

                $securityLogger->log(
                    'Password successfully reset.',
                    [
                        'user_id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'ip' => $request->getClientIp(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::NOTICE
                );

                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/reset_password.html.twig', [
                'form' => $form->createView(),
                'token' => $token
            ]);

        } catch (Throwable $e) {
            $securityLogger->log(
                'Unexpected error during password reset.',
                [
                    'exception' => $e,
                    'token' => $token,
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

}