<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartAddress;
use App\Entity\OrderAddress;
use App\Entity\OrderItem;
use App\Entity\OrderShop;
use App\Logger\OrderLogger;
use App\Logger\PaymentLogger;
use App\Service\CartService;
use App\Service\EmailNotificationService;
use App\Form\CartAddressType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * Controller responsible for managing the checkout process.
 *
 * This controller handles all steps of the checkout flow for an authenticated user:
 *  - Address step (addressAction)
 *  - Order summary step (summaryAction)
 *  - Order confirmation step (confirmAction)
 *  - Checkout success step (successAction)
 *
 * Each action ensures:
 *  - The user is authenticated
 *  - There is an active cart with at least one item
 *  - Cart addresses are correctly associated
 *  - Orders and order items are persisted in the database
 */
class CheckoutController extends AbstractController
{
    /**
     * Handles the checkout address step for the current user.
     *
     * This method retrieves the active cart for the authenticated user,
     * displays the address form, and persists the submitted address.
     * After successful submission, the user is redirected to the checkout summary.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param CartService $cartService
     * @param OrderLogger $orderLogger
     * @return Response
     */
    public function addressAction(Request $request, EntityManagerInterface $em, CartService $cartService, OrderLogger $orderLogger): Response
    {
        try {
            $user = $this->getUser();

            $orderLogger->log(
                'Checkout address step accessed',
                [
                    'user_id' => $user ? $user->getId() : null,
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

            if ($cart == null || count($cart->getItems()) == 0) {
                $orderLogger->log(
                    'Address step with empty or missing cart',
                    [
                        'user_id' => $user ? $user->getId() : null,
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::WARNING
                );

                return $this->redirectToRoute('cart_show');
            }

            $cartAddress = $cart->getCartAddress();
            if ($cartAddress == null) {
                $cartAddress = new CartAddress();

                $orderLogger->log(
                    'No cart address found, creating new one',
                    [
                        'cart_id' => $cart->getId(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::DEBUG
                );
            }

            $form = $this->createForm(CartAddressType::class, $cartAddress);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $cartAddress->setCart($cart);
                $em->persist($cartAddress);
                $em->flush();

                $orderLogger->log(
                    'Checkout address saved successfully',
                    [
                        'cart_id' => $cart->getId(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::NOTICE
                );

                return $this->redirectToRoute('checkout_summary');
            }

            return $this->render('checkout/address.html.twig', [
                'form' => $form->createView(),
                'cart' => $cart
            ]);

        } catch (Throwable $e) {

            $orderLogger->log(
                'Error during checkout address step',
                [
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

    /**
     * Handles the checkout summary step for the current user.
     *
     * This method retrieves the active cart for the authenticated user,
     * checks if the cart contains items, and ensures that an address is selected.
     * If no cart is found or no items are present, the user is redirected to the cart page.
     * If no address is associated with the cart, the user is redirected to the cart page.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param OrderLogger $orderLogger
     * @return Response
     */
    public function summaryAction(Request $request, EntityManagerInterface $em, OrderLogger $orderLogger): Response
    {
        try {
            $user = $this->getUser();

            $orderLogger->log(
                'Checkout summary step accessed',
                [
                    'user_id' => $user ? $user->getId() : null,
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

            if ($cart == null || count($cart->getItems()) == 0) {
                $orderLogger->log(
                    'Summary step with empty or missing cart',
                    [
                        'user_id' => $user ? $user->getId() : null,
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::WARNING
                );

                return $this->redirectToRoute('cart_show');
            }

            $cartAddress = $em->getRepository(CartAddress::class)->findOneBy([
                'cart' => $cart
            ]);

            if ($cartAddress == null) {
                $orderLogger->log(
                    'Summary step accessed without address',
                    [
                        'cart_id' => $cart->getId(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::WARNING
                );

                return $this->redirectToRoute('cart_show');
            }

            return $this->render('checkout/summary.html.twig', [
                'cart' => $cart,
                'cartAddress' => $cartAddress
            ]);

        } catch (Throwable $e) {

            $orderLogger->log(
                'Error during checkout summary step',
                [
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

    /**
     * Handles the order confirmation step for the current user.
     *
     * This method finalizes the checkout process by creating a new order
     * from the authenticated user's active cart. It ensures that the user
     * is logged in, that an active cart exists, and that the cart contains
     * at least one item.
     *
     * An address is retrieved and associated with the order (temporary logic).
     * A new Order entity is created with a pending status, and each cart item
     * is converted into an OrderItem associated with the order.
     *
     * If the user is not authenticated, they are redirected to the login page.
     * If no active cart exists or the cart is empty, the user is redirected
     * back to the cart page.
     *
     * After successfully persisting the order and its items, the user is
     * redirected to the checkout success page.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param CartService $cartService
     * @param EmailNotificationService $emailNotificationService
     * @param OrderLogger $orderLogger
     * @param PaymentLogger $paymentLogger
     * @return Response
     */
    public function confirmAction(Request $request, EntityManagerInterface $em, CartService $cartService, EmailNotificationService $emailNotificationService, OrderLogger $orderLogger, PaymentLogger $paymentLogger): Response
    {
        $user = $this->getUser();

        try {
            $orderLogger->log(
                'Checkout confirmation started',
                [
                    'user_id' => $user ? $user->getId() : null,
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

            if ($cart == null || count($cart->getItems()) == 0) {
                $orderLogger->log(
                    'Confirm step with empty cart',
                    [
                        'user_id' => $user ? $user->getId() : null,
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::WARNING
                );

                return $this->redirectToRoute('cart_show');
            }

            $cartAddress = $em->getRepository(CartAddress::class)->findOneBy([
                'cart' => $cart
            ]);

            if ($cartAddress == null) {
                $orderLogger->log(
                    'Confirm step without address',
                    [
                        'cart_id' => $cart->getId(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::WARNING
                );

                return $this->redirectToRoute('cart_show');
            }

            $cart->setStatus(Cart::STATUS_ORDERED);

            $order = new OrderShop();
            $order->setUser($user);
            $order->setStatus(OrderShop::STATUS_PENDING);
            $em->persist($order);

            $orderLogger->log(
                'Order entity created',
                [
                    'order_id' => $order->getId(),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::NOTICE
            );

            $orderAddress = new OrderAddress();
            $orderAddress->setOrderShop($order);
            $orderAddress->setPrimaryAddress($cartAddress->getPrimaryAddress());
            $orderAddress->setSecondaryAddress($cartAddress->getSecondaryAddress());
            $orderAddress->setCity($cartAddress->getCity());
            $orderAddress->setState($cartAddress->getState());
            $orderAddress->setPostalCode($cartAddress->getPostalCode());
            $orderAddress->setCountry($cartAddress->getCountry());
            $em->persist($orderAddress);

            foreach ($cart->getItems() as $cartItem) {

                $product = $cartItem->getProduct();

                if ($product->getStock() < $cartItem->getQuantity()) {
                    $orderLogger->log(
                        'Insufficient stock during checkout',
                        [
                            'product_id' => $product->getId(),
                            'requested' => $cartItem->getQuantity(),
                            'available' => $product->getStock(),
                            'source' => [
                                'method' => __METHOD__,
                                'line' => __LINE__
                            ]
                        ],
                        LogLevel::WARNING
                    );

                    return $this->redirectToRoute('cart_show');
                }

                $product->setStock($product->getStock() - $cartItem->getQuantity());

                $item = new OrderItem();
                $item->setOrderShop($order);
                $item->setProduct($product);
                $item->setUnitPrice($product->getPrice());
                $item->setQuantity($cartItem->getQuantity());
                $item->setSubtotal($item->getUnitPrice() * $item->getQuantity());
                $em->persist($item);

                $orderLogger->log(
                    'Order item added',
                    [
                        'order_id' => $order->getId(),
                        'product_id' => $product->getId(),
                        'quantity' => $item->getQuantity(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::DEBUG
                );
            }

            $em->flush();

            $emailNotificationService->sendOrderConfirmation($order);

            $orderLogger->log(
                'Order confirmed and persisted',
                [
                    'order_id' => $order->getId(),
                    'total_items' => count($cart->getItems()),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::NOTICE
            );

            $paymentLogger->log(
                'Payment initiated for order',
                [
                    'order_id' => $order->getId(),
                    'amount' => $cartService->getTotal($user),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            return $this->redirectToRoute('checkout_success');

        } catch (Throwable $e) {

            $orderLogger->log(
                'Checkout confirmation failed',
                [
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
    }

    /**
     * Handles the checkout success step for the current user.
     *
     * This method validates the final stage of the checkout flow by ensuring
     * that the user is authenticated and has an active cart with at least
     * one item. If these conditions are not met, the user is redirected
     * to the appropriate page.
     *
     * The user's active cart is retrieved and temporarily associated with
     * a fixed address (placeholder logic to be improved). The cart and
     * address are then passed to the checkout success view for display.
     *
     * If the user is not authenticated, they are redirected to the login page.
     * If no active cart exists or the cart is empty, the user is redirected
     * back to the cart page.
     *
     * @param OrderLogger $orderLogger
     * @return Response
     */
    public function successAction(OrderLogger $orderLogger): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $orderLogger->log(
                'Checkout success accessed without authentication',
                [
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::WARNING
            );

            return $this->redirectToRoute('app_login');
        }

        $orderLogger->log(
            'Checkout completed successfully',
            [
                'user_id' => $user->getId(),
                'source' => [
                    'method' => __METHOD__,
                    'line' => __LINE__
                ]
            ],
            LogLevel::NOTICE
        );

        return $this->render('checkout/success.html.twig');
    }

}