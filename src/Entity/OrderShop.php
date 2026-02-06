<?php

namespace App\Entity;

use App\Entity\OrderAddress;
use App\Entity\OrderItem;
use App\Entity\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * OrderShop
 */
class OrderShop
{
    /**
     * The order is created but has not been completed by the user within the expected timeframe.
     * This status typically indicates that the user started the checkout process but did not finish it,
     * resulting in an expired order after a specified period (e.g., 30 days).
     */
    public const STATUS_PENDING = 'pending';

    /**
     * The order has been received and is currently being processed by the store.
     * This status typically indicates that the payment has been confirmed, and the items are being prepared 
     * for shipment or are awaiting confirmation from external systems (e.g., inventory checks or payment verification).
     */
    public const STATUS_PROCESSING = 'processing';

    /**
     * The order has been shipped and is on its way to the customer.
     * This status indicates that the package has left the warehouse or fulfillment center, and the customer 
     * should expect delivery soon. Shipping tracking information may be available at this point.
     */
    public const STATUS_SHIPPED = 'shipped';

    /**
     * The order has been successfully delivered to the customer.
     * This status indicates that the package has arrived at the customer's address and the delivery process is complete.
     * The order is now considered fulfilled.
     */
    public const STATUS_DELIVERED = 'delivered';

    /**
     * The order has been cancelled, either by the customer or by the store due to an issue such as an inventory problem or an order modification.
     * This status indicates that the order will not proceed, and no further processing or shipment will take place.
     */
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The order has been refunded, typically due to a return or dispute.
     * This status indicates that the customer has been reimbursed for the order, and the payment has been returned, either fully or partially.
     */
    public const STATUS_REFUNDED = 'refunded';

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var User
     */
    private $user;

    /**
     * @var OrderAddress
     */
    private $orderAddress;

    /**
     * The current status of the order, which indicates whether the order is active, ordered, or expired.
     * 
     * Possible values include:
     * - STATUS_PENDING: The order has been created but not completed by the user within the expected timeframe.
     * - STATUS_PROCESSING: The order is being processed and prepared for shipment after payment confirmation.
     * - STATUS_SHIPPED: The order has been shipped and is on its way to the customer.
     * - STATUS_DELIVERED: The order has been delivered to the customer.
     * - STATUS_CANCELLED: The order has been cancelled and will not proceed.
     * - STATUS_REFUNDED: The order has been refunded to the customer, typically due to a return or dispute.
     * 
     * This field tracks the progression of the order through different stages of the purchase lifecycle.
     *
     * @var string
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var Datetime
     */
    private $createdAt;

    /**
     * @var Datetime
     */
    private $updatedAt;

    /**
     * @var Collection|OrderItem[] 
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return OrderAddress
     */
    public function getOrderAddress(): OrderAddress
    {
        return $this->orderAddress;
    }

    /**
     * @param OrderAddress $orderAddress
     *
     * @return $this
     */
    public function setOrderAddress(OrderAddress $orderAddress): self
    {
        $this->orderAddress = $orderAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return Datetime
     */
    public function getCreatedAt(): Datetime
    {
        return $this->createdAt;
    }

    /**
     * @param Datetime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(Datetime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Datetime
     */
    public function getUpdatedAt(): Datetime
    {
        return $this->updatedAt;
    }

    /**
     * @param Datetime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(Datetime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get the items of the order.
     *
     * @return Collection|OrderItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Add a item to the order.
     *
     * @param OrderItem $item
     * @return $this
     */
    public function addItem(OrderItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setCategory($this);
        }

        return $this;
    }

    /**
     * Remove a item from the order.
     *
     * @param OrderItem $item
     * @return $this
     */
    public function removeItem(OrderItem $item): self
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            if ($item->getOrder() == $this) {
                $item->setOrder(null);
            }
        }

        return $this;
    }

}