<?php

namespace App\Entity;

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
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return $this
     */
    public function setName(?string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string|null $sku
     *
     * @return $this
     */
    public function setSku(?string $sku)
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * @param float|null $minPrice
     *
     * @return $this
     */
    public function setMinPrice(?float $minPrice)
    {
        $this->minPrice = $minPrice;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getMaxPrice()
    {
        return $this->maxPrice;
    }

    /**
     * @param float|null $maxPrice
     *
     * @return $this
     */
    public function setMaxPrice(?float $maxPrice)
    {
        $this->maxPrice = $maxPrice;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param int|null $stock
     *
     * @return $this
     */
    public function setStock(?int $stock)
    {
        $this->stock = $stock;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param bool|null $isActive
     *
     * @return $this
     */
    public function setIsActive(?bool $isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return Datetime|null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param Datetime|null $startDate
     *
     * @return $this
     */
    public function setStartDate(?Datetime $startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return Datetime|null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param Datetime|null $endDate
     *
     * @return $this
     */
    public function setEndDate(?Datetime $endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

}