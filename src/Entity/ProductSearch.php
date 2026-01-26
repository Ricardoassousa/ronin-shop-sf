<?php

namespace App\Entity;

use App\Entity\Category;
use DateTime;

/**
 * ProductSearch
 */
class ProductSearch
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * Stock Keeping Unit (unique internal identifier).
     *
     * @var string|null
     */
    private $sku;

    /**
     * @var string|null
     */
    private $shortDescription;

    /**
     * @var float|null
     */
    private $minPrice;

    /**
     * @var float|null
     */
    private $maxPrice;

    /**
     * @var int|null
     */
    private $stock;

    /**
     * Whether the product is active and visible.
     * 
     * @var bool|null
     */
    private $isActive = true;

    /**
     * @var Datetime|null
     */
    private $startDate;

    /**
     * @var Datetime|null
     */
    private $endDate;

    /**
     * @var Category|null
     */
    private $category;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return $this
     */
    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * @param string|null $sku
     *
     * @return $this
     */
    public function setSku(?string $sku): self
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @param string|null $shortDescription
     *
     * @return $this
     */
    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getMinPrice(): ?float
    {
        return $this->minPrice;
    }

    /**
     * @param float|null $minPrice
     *
     * @return $this
     */
    public function setMinPrice(?float $minPrice): self
    {
        $this->minPrice = $minPrice;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getMaxPrice(): ?float
    {
        return $this->maxPrice;
    }

    /**
     * @param float|null $maxPrice
     *
     * @return $this
     */
    public function setMaxPrice(?float $maxPrice): self
    {
        $this->maxPrice = $maxPrice;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getStock(): ?int
    {
        return $this->stock;
    }

    /**
     * @param int|null $stock
     *
     * @return $this
     */
    public function setStock(?int $stock): self
    {
        $this->stock = $stock;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * @param bool|null $isActive
     *
     * @return $this
     */
    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return Datetime|null
     */
    public function getStartDate(): ?Datetime
    {
        return $this->startDate;
    }

    /**
     * @param Datetime|null $startDate
     *
     * @return $this
     */
    public function setStartDate(?Datetime $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return Datetime|null
     */
    public function getEndDate(): ?Datetime
    {
        return $this->endDate;
    }

    /**
     * @param Datetime|null $endDate
     *
     * @return $this
     */
    public function setEndDate(?Datetime $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category|null $endDate
     *
     * @return $this
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }

}