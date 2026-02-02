<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Logger\CartLogger;
use App\Logger\StockLogger;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\HttpFoundation\Exception\InvalidArgumentException;

/**
 * Service to manage the shopping cart operations such as adding, removing, updating products, 
 * and calculating the total price.
 */
class CartService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var CartLogger
     */
    private $cartLogger;

    /**
     * @var StockLogger
     */
    private $stockLogger;

    /**
     * CartService constructor.
     *
     * @param EntityManagerInterface $em
     * @param CartLogger $cartLogger
     * @param StockLogger $stockLogger
     */
    public function __construct(EntityManagerInterface $em, CartLogger $cartLogger, StockLogger $stockLogger)
    {
        $this->em = $em;
        $this->cartLogger = $cartLogger;
        $this->stockLogger = $stockLogger;
    }

    /**
     * Get or create the user's active cart.
     *
     * @param User $user
     * @return Cart
     */
    public function getCart(User $user): Cart
    {
        $this->cartLogger->log(
            'Active cart initialized',
            [
                'user_id' => $user->getId(),
                'cart_id' => $cart->getId(),
                'source' => [
                    'method' => __METHOD__,
                    'line' => __LINE__
                ]
            ],
            LogLevel::NOTICE
        );

        $cart = $this->em->getRepository(Cart::class)->findOneBy([
            'user' => $user,
            'status' => Cart::STATUS_ACTIVE
        ]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setStatus(Cart::STATUS_ACTIVE);

            $this->em->persist($cart);
            $this->em->flush();

            $this->cartLogger->log(
                'Created new active cart for user',
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

        return $cart;
    }

    /**
     * Add a product to the user's cart.
     *
     * @param User $user
     * @param Product $product
     * @param int $quantity
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function addProduct(User $user, Product $product, int $quantity = 1): void
    {
        // Quantity validation
        if ($quantity < 1) {
            $this->stockLogger->log(
                'Invalid quantity to add to cart',
                [
                    'user_id' => $user->getId(),
                    'product_id' => $product->getId(),
                    'quantity' => $quantity,
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::ERROR
            );
            throw new InvalidArgumentException('Quantity must be at least 1.');
        }

        // Check available stock
        if ($product->getStock() < $quantity) {
            $this->stockLogger->log(
                'Not enough stock for the product',
                [
                    'user_id' => $user->getId(),
                    'product_id' => $product->getId(),
                    'requested_quantity' => $quantity,
                    'available_stock' => $product->getStock(),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::ERROR
            );
            throw new LogicException('Not enough stock for this product.');
        }

        // Get or create active cart for the user
        $cart = $this->getCart($user);

        // Check if the product already exists in the cart
        $item = $this->em->getRepository(CartItem::class)->findOneBy([
            'cart' => $cart,
            'product' => $product
        ]);

        if ($item) {
            $newQuantity = $item->getQuantity() + $quantity;

            // Check stock again
            if ($newQuantity > $product->getStock()) {
                $this->stockLogger->log(
                    'Not enough stock to add more units of product',
                    [
                        'user_id' => $user->getId(),
                        'product_id' => $product->getId(),
                        'requested_quantity' => $newQuantity,
                        'available_stock' => $product->getStock(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::ERROR
                );
                throw new LogicException('Not enough stock to add more units.');
            }

            $item->setQuantity($newQuantity);

            $this->cartLogger->log(
                'Updated product quantity in cart',
                [
                    'user_id' => $user->getId(),
                    'product_id' => $product->getId(),
                    'new_quantity' => $newQuantity,
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );
        } else {
            // Create new cart item
            $item = new CartItem();
            $item->setCart($cart);
            $item->setProduct($product);
            $item->setQuantity($quantity);

            $this->em->persist($item);

            $this->cartLogger->log(
                'Added product to cart',
                [
                    'user_id' => $user->getId(),
                    'product_id' => $product->getId(),
                    'quantity' => $quantity,
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );
        }

        // Persist changes
        $this->em->flush();
    }

    /**
     * Remove a product from the user's cart.
     *
     * @param User $user
     * @param Product $product
     * @param int|null $quantity
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    public function removeProduct(User $user, Product $product, ?int $quantity = null): void
    {
        $cart = $this->getCart($user);

        $item = $this->em->getRepository(CartItem::class)->findOneBy([
            'cart' => $cart,
            'product' => $product
        ]);

        if (!$item) {
            $this->cartLogger->log(
                'Attempt to remove product not present in cart',
                [
                    'user_id' => $user->getId(),
                    'product_id' => $product->getId(),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::WARNING
            );

            throw new LogicException('Product not found in the cart.');
        }

        if ($quantity != null) {
            if ($quantity < 1) {
                $this->cartLogger->log(
                    'Invalid quantity provided to remove product from cart',
                    [
                        'user_id' => $user->getId(),
                        'product_id' => $product->getId(),
                        'quantity' => $quantity,
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::ERROR
                );

                throw new InvalidArgumentException('Quantity to remove must be at least 1.');
            }

            if ($quantity < $item->getQuantity()) {
                $item->setQuantity($item->getQuantity() - $quantity);

                $this->cartLogger->log(
                    'Reduced product quantity in cart',
                    [
                        'user_id' => $user->getId(),
                        'product_id' => $product->getId(),
                        'removed_quantity' => $quantity,
                        'remaining_quantity' => $item->getQuantity(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::INFO
                );
            } else {
                $this->em->remove($item);

                $this->cartLogger->log(
                    'Removed product entirely from cart',
                    [
                        'user_id' => $user->getId(),
                        'product_id' => $product->getId(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::NOTICE
                );
            }
        } else {
            $this->em->remove($item);

            $this->cartLogger->log(
                'Removed product from cart (no quantity specified)',
                [
                    'user_id' => $user->getId(),
                    'product_id' => $product->getId(),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::NOTICE
            );
        }

        $this->em->flush();
    }

    /**
     * Update the quantity of a product in the user's cart.
     *
     * @param User $user
     * @param Product $product
     * @param int $quantity
     * @throws LogicException
     */
    public function updateQuantity(User $user, Product $product, int $quantity): void
    {
        $cart = $this->getCart($user);

        $item = $this->em->getRepository(CartItem::class)->findOneBy([
            'cart' => $cart,
            'product' => $product
        ]);

        if (!$item) {
            $this->cartLogger->log(
                'Attempt to update quantity for product not in cart',
                [
                    'user_id' => $user->getId(),
                    'product_id' => $product->getId(),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::WARNING
            );

            throw new LogicException('Product not found in the cart.');
        }

        if ($quantity <= 0) {
            $this->cartLogger->log(
                'Quantity set to zero or less, removing product from cart',
                [
                    'user_id' => $user->getId(),
                    'product_id' => $product->getId(),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::NOTICE
            );

            $this->removeProduct($user, $product);
            return;
        }

        if ($quantity > $product->getStock()) {
            $this->stockLogger->log(
                'Insufficient stock while updating cart quantity',
                [
                    'user_id' => $user->getId(),
                    'product_id' => $product->getId(),
                    'requested_quantity' => $quantity,
                    'available_stock' => $product->getStock(),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::WARNING
            );

            throw new LogicException('Not enough stock for this quantity.');
        }

        $item->setQuantity($quantity);
        $this->em->flush();

        $this->cartLogger->log(
            'Updated product quantity in cart',
            [
                'user_id' => $user->getId(),
                'product_id' => $product->getId(),
                'new_quantity' => $quantity,
                'source' => [
                    'method' => __METHOD__,
                    'line' => __LINE__
                ]
            ],
            LogLevel::INFO
        );
    }

    /**
     * Calculate the total price of all items in the user's cart.
     *
     * @param User $user
     * @return float
     */
    public function getTotal(User $user): float
    {
        $cart = $this->getCart($user);
        $total = 0;

        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();

            if (!$product) {
                $this->cartLogger->log(
                    'Cart item without product detected',
                    [
                        'user_id' => $user->getId(),
                        'cart_item_id' => $item->getId(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::WARNING
                );
                continue;
            }

            $price = max(0, $product->getPrice());
            $quantity = max(0, $item->getQuantity());

            $total += $price * $quantity;
        }

        return $total;
    }

}