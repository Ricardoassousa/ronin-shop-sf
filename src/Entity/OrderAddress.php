<?php

namespace App\Entity;

use App\Entity\OrderShop;
use App\Entity\User;

/**
 * OrderAddress
 */
class OrderAddress
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $primaryAddress;

    /**
     * @var string
     */
    private $secondaryAddress;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string|null
     */
    private $state;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var string
     */
    private $country;

    /**
     * @var OrderShop
     */
    private $orderShop;

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
    public function getPrimaryAddress(): string
    {
        return $this->primaryAddress;
    }

    /**
     * @param string $primaryAddress
     *
     * @return $this
     */
    public function setPrimaryAddress(string $primaryAddress): self
    {
        $this->primaryAddress = $primaryAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecondaryAddress(): string
    {
        return $this->secondaryAddress;
    }

    /**
     * @param string $secondaryAddress
     *
     * @return $this
     */
    public function setSecondaryAddress(string $secondaryAddress): self
    {
        $this->secondaryAddress = $secondaryAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return $this
     */
    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     *
     * @return $this
     */
    public function setState(?string $state): self
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     *
     * @return $this
     */
    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return $this
     */
    public function setCountry(string $country): self
    {
        $this->country = $country;
        return $this;
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

}