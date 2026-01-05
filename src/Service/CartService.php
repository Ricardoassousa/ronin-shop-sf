<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
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
     *
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Get or create the user's active cart.
     *
     * @param User $user
     * @return Cart
     */
    public function getCart(User $user): Cart
    {
        $cart = $this->em->getRepository(Cart::class)->findOneBy([
            'user' => $user,
            'status' => 'active'
        ]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setStatus('active');

            $this->em->persist($cart);
            $this->em->flush();
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
            throw new InvalidArgumentException('Quantity must be at least 1.');
        }

        // Check available stock
        if ($product->getStock() < $quantity) {
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
                throw new LogicException('Not enough stock to add more units.');
            }

            $item->setQuantity($newQuantity);
        } else {
            // Create new cart item
            $item = new CartItem();
            $item->setCart($cart);
            $item->setProduct($product);
            $item->setQuantity($quantity);

            $this->em->persist($item);
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

        // Find the item directly
        $item = $this->em->getRepository(CartItem::class)->findOneBy([
            'cart' => $cart,
            'product' => $product
        ]);

        if (!$item) {
            throw new LogicException('Product not found in the cart.');
        }

        // Validate quantity if provided
        if ($quantity !== null) {
            if ($quantity < 1) {
                throw new InvalidArgumentException('Quantity to remove must be at least 1.');
            }

            if ($quantity < $item->getQuantity()) {
                // Remove only part of the quantity
                $item->setQuantity($item->getQuantity() - $quantity);
            } else {
                // Remove the entire item
                $this->em->remove($item);
            }
        } else {
            // No quantity defined → remove the entire item
            $this->em->remove($item);
        }

        // Persist changes
        $this->em->flush();
    }

    /**
     * Update the quantity of a product in the user's cart.
     *
     * @param User $user
     * @param Product $product
     * @param int $quantity
     * @throws LogicException
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
            throw new LogicException('Product not found in the cart.');
        }

        // If the quantity is <= 0, remove the item
        if ($quantity <= 0) {
            $this->removeProduct($user, $product);
            return;
        }

        // Check stock
        if ($quantity > $product->getStock()) {
            throw new LogicException('Not enough stock for this quantity.');
        }

        // Update quantity
        $item->setQuantity($quantity);

        $this->em->flush();
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
            if (!$product) continue; // Ignore invalid items

            $price = max(0, $product->getPrice());
            $quantity = max(0, $item->getQuantity());

            $total += $price * $quantity;
        }

        return $total;
    }

}