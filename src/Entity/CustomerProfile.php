<?php

namespace App\Entity;

use App\Entity\User;
use DateTime;

/**
 * CustomerProfile
 */
class CustomerProfile
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $surname;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $countryPrefixCode;

    /**
     * @var string
     */
    private $primaryAddress;

    /**
     * @var string|null
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
     * @var User
     */
    private $user;

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
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param string $user
     *
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return $this
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     *
     * @return $this
     */
    public function setSurname(string $surname): self
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return $this
     */
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryPrefixCode(): string
    {
        return $this->countryPrefixCode;
    }

    /**
     * @param string $countryPrefixCode
     *
     * @return $this
     */
    public function setCountryPrefixCode(string $countryPrefixCode): self
    {
        $this->countryPrefixCode = $countryPrefixCode;
        return $this;
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
     * @return string|null
     */
    public function getSecondaryAddress(): ?string
    {
        return $this->secondaryAddress;
    }

    /**
     * @param string|null $secondaryAddress
     *
     * @return $this
     */
    public function setSecondaryAddress(?string $secondaryAddress): self
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