<?php

namespace App\Entity;

use App\Entity\Cart;
use App\Entity\Product;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * CartItem
 */
class CartItem
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var int
     */
    private $quantity;
    
    /**
     * @var Datetime
     */
    private $createdAt;

    /**
     * @var Datetime
     */
    private $updatedAt;

    /**
     *
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param Cart $cart
     *
     * @return $this
     */
    public function setCart(Cart $cart)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     *
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return $this
     */
    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param Datetime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(Datetime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param Datetime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(Datetime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

}