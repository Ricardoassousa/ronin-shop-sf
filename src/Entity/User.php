<?php

namespace App\Entity;

use App\Entity\Cart;
use App\Entity\CustomerProfile;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * User
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var array
     */
    private $roles = array();

    /**
     * @var string
     */
    private $password;

    /**
     * @var CustomerProfile
     */
    private $customerProfile;

    /**
     * @var Collection|Cart[]
     */
    private $carts;

    /**
     *
     */
    public function __construct()
    {
        $this->carts = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles ? $this->roles : ['ROLE_USER'];
    }

    /**
     * @param string $roles
     *
     * @return $this
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getUserIdentifier()
    {
        return $this->email;
    }

    /**
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials() { }

    /**
     * @return CustomerProfile
     */
    public function getCustomerProfile(): ?CustomerProfile
    {
        return $this->customerProfile;
    }

    /**
     * @param CustomerProfile $customerProfile
     *
     * @return $this
     */
    public function setCustomerProfile(?CustomerProfile $customerProfile): self
    {
        if ($customerProfile->getUser() != $this) {
            $customerProfile->setUser($this);
        }
        $this->customerProfile = $customerProfile;

        return $this;
    }

    /**
     * Get the carts of the user.
     *
     * @return Collection|Cart[]
     */
    public function getCarts()
    {
        return $this->carts;
    }

    /**
     * Add a cart to the user.
     *
     * @param Cart $cart
     * @return $this
     */
    public function addCart(Cart $cart)
    {
        if (!$this->carts->contains($cart)) {
            $this->carts[] = $cart;
            $cart->setCategory($this);
        }

        return $this;
    }

    /**
     * Remove a cart from the user.
     *
     * @param Cart $cart
     * @return $this
     */
    public function removeCart(Cart $cart)
    {
        if ($this->carts->contains($cart)) {
            $this->carts->removeElement($cart);
            if ($cart->getCart() == $this) {
                $cart->setCart(null);
            }
        }

        return $this;
    }

}