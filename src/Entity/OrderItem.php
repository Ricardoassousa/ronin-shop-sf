<?php

namespace App\Entity;

use App\Entity\OrderShop;
use App\Entity\Product;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * OrderItem
 */
class OrderItem
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var OrderShop
     */
    private $orderShop;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var string
     */
    private $productName;

    /**
     * URL-friendly version of the product name.
     *
     * @var string|null
     */
    private $productSlug;

    /**
     * Stock Keeping Unit (unique internal identifier).
     *
     * @var string
     */
    private $productSku;

    /**
     * @var string|null
     */
    private $productShortDescription;

    /**
     * @var string|null
     */
    private $productImage;

    /**
     * @var string
     */
    private $unitPrice;

    /**
     * @var string
     */
    private $subtotal;

    /**
     * @var string
     */
    private $discount;

    /**
     * @var string
     */
    private $finalPrice;

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
     * Constructor to initialize default values for the entity.
     *
     * This constructor sets the creation date to the current DateTime.
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return OrderShop
     */
    public function getOrderShop(): OrderShop
    {
        return $this->orderShop;
    }

    /**
     * @param OrderShop $orderShop
     *
     * @return $this
     */
    public function setOrderShop(OrderShop $orderShop): self
    {
        $this->orderShop = $orderShop;
        return $this;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     *
     * @return $this
     */
    public function setProduct(Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductName(): string
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     *
     * @return $this
     */
    public function setProductName(string $productName): self
    {
        $this->productName = $productName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProductSlug(): ?string
    {
        return $this->productSlug;
    }

    /**
     * @param string|null $productSlug
     *
     * @return $this
     */
    public function setProductSlug(?string $productSlug): self
    {
        $this->productSlug = $productSlug;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductSku(): string
    {
        return $this->productSku;
    }

    /**
     * @param string $productSku
     *
     * @return $this
     */
    public function setProductSku(string $productSku): self
    {
        $this->productSku = $productSku;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProductShortDescription(): ?string
    {
        return $this->productShortDescription;
    }

    /**
     * @param string|null $productShortDescription
     *
     * @return $this
     */
    public function setProductShortDescription(?string $productShortDescription): self
    {
        $this->productShortDescription = $productShortDescription;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProductImage(): ?string
    {
        return $this->productImage;
    }

    /**
     * @param string|null $productImage
     *
     * @return $this
     */
    public function setProductImage(?string $productImage): self
    {
        $this->productImage = $productImage;
        return $this;
    }

    /**
     * @return string
     */
    public function getUnitPrice(): string
    {
        return $this->unitPrice;
    }

    /**
     * @param string $unitPrice
     *
     * @return $this
     */
    public function setUnitPrice(string $unitPrice): self
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubtotal(): string
    {
        return $this->subtotal;
    }

    /**
     * @param string $subtotal
     *
     * @return $this
     */
    public function setSubtotal(string $subtotal): self
    {
        $this->subtotal = $subtotal;
        return $this;
    }

    /**
     * @return string
     */
    public function getDiscount(): string
    {
        return $this->discount;
    }

    /**
     * @param string $discount
     *
     * @return $this
     */
    public function setDiscount(string $discount): self
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @return string
     */
    public function getFinalPrice(): string
    {
        return $this->finalPrice;
    }

    /**
     * @param string $finalPrice
     *
     * @return $this
     */
    public function setFinalPrice(string $finalPrice): self
    {
        $this->finalPrice = $finalPrice;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return $this
     */
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
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

}