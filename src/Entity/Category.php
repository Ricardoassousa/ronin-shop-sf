<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Category
 */
class Category
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * URL-friendly version of the category name.
     *
     * @var string
     */
    private $slug;

    /**
     * @var string|null
     */
    private $description;

    /**
     * Whether the category is active and visible.
     * 
     * @var bool
     */
    private $isActive = true;

    /**
     * @var Datetime
     */
    private $createdAt;

    /**
     * @var Datetime
     */
    private $updatedAt;

    /**
     * @var Collection|Product[] 
     */
    private $products;

    /**
     * Constructor to initialize default values for the entity.
     *
     * This constructor sets the creation date to the current DateTime
     * and initializes the items collection to ensure it is ready
     * for use when adding or removing related entities.
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->products = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return $this
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
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
     * Get the products of the category.
     *
     * @return Collection|Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Add a product to the category.
     *
     * @param Product $product
     * @return $this
     */
    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setCategory($this);
        }

        return $this;
    }

    /**
     * Remove a product from the category.
     *
     * @param Product $product
     * @return $this
     */
    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * Returns the string representation of the Category.
     *
     * This method is called when the object is treated as a string,
     * for example in forms or when echoing the object.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }

}