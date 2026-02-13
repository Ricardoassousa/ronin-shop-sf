<?php

namespace App\EventListener;

use App\Logger\SecurityLogger;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Throwable;

class LoginListener
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SecurityLogger
     */
    private $securityLogger;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * LoginListener constructor.
     *
     * This constructor injects the necessary services to handle login events:
     * - EntityManagerInterface to persist changes to the user entity.
     * - SecurityLogger to log security-related actions.
     * - RequestStack to access the current HTTP request for IP and user-agent information.
     *
     * @param EntityManagerInterface $em The entity manager service.
     * @param SecurityLogger $securityLogger Custom logger for security events.
     * @param RequestStack $requestStack Service to access the current HTTP request.
     */
    public function __construct(EntityManagerInterface $em, SecurityLogger $securityLogger, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->securityLogger = $securityLogger;
        $this->requestStack = $requestStack;
    }

    /**
     * Handles the security.interactive_login event.
     *
     * This method is executed when a user successfully logs in. It performs the following actions:
     * - Updates the user's last login timestamp.
     * - Flushes the changes to the database.
     * - Logs the login action with relevant details such as user ID, IP address, user-agent, and source location.
     *
     * @param InteractiveLoginEvent $even
     * @return void
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        $request = $this->requestStack->getCurrentRequest();

        if ($user instanceof UserInterface) {
            try {
                $user->setLastLoginAt(new DateTime());
                $this->em->flush();

                $this->securityLogger->log(
                    'User logged in successfully.',
                    [
                        'user_id' => $user->getId(),
                        'ip' => $request ? $request->getClientIp() : 'N/A',
                        'user_agent' => $request ? $request->headers->get('User-Agent') : 'N/A',
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::INFO
                );
            } catch (Throwable $e) {
                $this->securityLogger->log(
                    'Failed to update last login timestamp.',
                    [
                        'user_id' => $user->getId(),
                        'error' => $e->getMessage()
                    ],
                    LogLevel::ERROR
                );
            }
        }
    }

}