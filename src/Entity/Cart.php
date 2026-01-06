<?php

namespace App\Entity;

use App\Entity\CartItem;
use App\Entity\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cart
 */
class Cart
{
    /**
     * The cart is active, meaning it is in use and has not been completed or expired.
     */
    public const STATUS_ACTIVE = 'active';

    /**
     * The cart has been ordered, meaning the user has completed the checkout process.
     */
    public const STATUS_ORDERED = 'ordered';

    /**
     * The cart has expired, meaning the user didn't complete the purchase in a given timeframe (30 days).
     */
    public const STATUS_EXPIRED = 'expired';

    /**
     * @var int
     */
    private $id;

    /**
     * @var User
     */
    private $user;

    /**
     * Whether the cart is active, ordered or expired.
     * 
     * @var string
     */
    private $status = self::STATUS_ACTIVE;

    /**
     * @var Datetime
     */
    private $createdAt;

    /**
     * @var Datetime
     */
    private $updatedAt;

    /**
     * @var Collection|CartItem[] 
     */
    private $items;

    /**
     *
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->items = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
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

    /**
     * Get the items of the cart.
     *
     * @return Collection|CartItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Add a item to the cart.
     *
     * @param CartItem $item
     * @return $this
     */
    public function addItem(CartItem $item)
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setCategory($this);
        }

        return $this;
    }

    /**
     * Remove a item from the cart.
     *
     * @param CartItem $item
     * @return $this
     */
    public function removeItem(CartItem $item)
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            if ($item->getCart() == $this) {
                $item->setCart(null);
            }
        }

        return $this;
    }

}