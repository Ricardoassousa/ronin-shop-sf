<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Logger\AnalyticsLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Controller responsible for handling default pages of the application.
 *
 * This includes the homepage and can be extended to include other
 * general-purpose pages that do not fit into a specific domain.
 */
class DefaultController extends AbstractController
{
    /**
     * Displays the homepage.
     *
     * This action renders the homepage template and passes a message to the view.
     * The message is displayed on the homepage and can be customized.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param AnalyticsLogger $analyticsLogger
     * @return Response
     */
    public function homepageAction(Request $request, EntityManagerInterface $em, AnalyticsLogger $analyticsLogger): Response
    {
        try {
            $user = $this->getUser();
            $cart = $user
                ? $em->getRepository(Cart::class)->findOneBy(['user' => $user, 'status' => Cart::STATUS_ACTIVE])
                : null;

            $analyticsLogger->log(
                'Homepage accessed',
                [
                    'ip' => $request->getClientIp(),
                    'user_agent' => $request->headers->get('User-Agent'),
                    'cart_items_count' => $cart ? count($cart->getItems()) : 0,
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            return $this->render('homepage.html.twig', [
                'cart' => $cart
            ]);
        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Unexpected error rendering homepage',
                [
                    'exception' => $e
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

}