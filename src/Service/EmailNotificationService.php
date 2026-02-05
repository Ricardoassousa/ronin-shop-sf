<?php

namespace App\Service;

use App\Entity\OrderShop;
use App\Entity\User;
use App\Logger\OrderLogger;
use Psr\Log\LogLevel;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Service responsible for sending transactional emails
 * such as order confirmations and other customer notifications.
 */
class EmailNotificationService
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var OrderLogger
     */
    private $orderLogger;

    /**
     * EmailNotificationService constructor.
     *
     * @param MailerInterface $mailer
     */
    public function __construct(MailerInterface $mailer, OrderLogger $orderLogger)
    {
        $this->mailer = $mailer;
        $this->orderLogger = $orderLogger;
    }

    /**
     * Sends an order confirmation email to the customer.
     *
     * @param OrderShop $order
     * @return void
     */
    public function sendOrderConfirmation(OrderShop $order): void
    {
        try {
            $email = (new TemplatedEmail())
                ->from('no-reply@mystore.com')
                ->to($order->getUser()->getEmail())
                ->subject('Your Order Confirmation #' . $order->getId())
                ->htmlTemplate('emails/order_confirmation.html.twig')
                ->context([
                    'order' => $order,
                ]);

            $this->mailer->send($email);

        } catch (TransportExceptionInterface $e) {
            $this->orderLogger->log(
                'Order confirmation email failed to send.',
                [
                    'order_id' => $order->getId(),
                    'user_email' => $order->getUser()->getEmail(),
                    'exception' => $e->getMessage(),
                ],
                LogLevel::ERROR
            );
        }
    }

}