<?php

namespace App\Entity;

use App\Entity\Cart;
use App\Entity\CustomerProfile;
use App\Entity\OrderShop;
use App\Entity\PasswordResetToken;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * User
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var int|null
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
     * @var Collection|OrderShop[]
     */
    private $orders;

    /**
     * @var Collection|PasswordResetToken[]
     */
    private $passwordResetTokens;

    /**
     *
     */
    public function __construct()
    {
        $this->carts = new ArrayCollection();
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
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles ? $this->roles : ['ROLE_USER'];
    }

    /**
     * @param string $roles
     *
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
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
     * @return CustomerProfile|null
     */
    public function getCustomerProfile(): ?CustomerProfile
    {
        return $this->customerProfile;
    }

    /**
     * @param CustomerProfile|null $customerProfile
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
    public function addCart(Cart $cart): self
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
    public function removeCart(Cart $cart): self
    {
        if ($this->carts->contains($cart)) {
            $this->carts->removeElement($cart);
            if ($cart->getCart() == $this) {
                $cart->setCart(null);
            }
        }

        return $this;
    }

    /**
     * Get the orders of the user.
     *
     * @return Collection|OrderShop[]
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Add a order to the user.
     *
     * @param OrderShop $order
     * @return $this
     */
    public function addOrder(OrderShop $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setCategory($this);
        }

        return $this;
    }

    /**
     * Remove a order from the user.
     *
     * @param OrderShop $order
     * @return $this
     */
    public function removeOrder(OrderShop $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            if ($order->getCart() == $this) {
                $order->setCart(null);
            }
        }

        return $this;
    }

    /**
     * Get the password reset tokens of the user.
     *
     * @return Collection|PasswordResetToken[]
     */
    public function getPasswordResetTokens()
    {
        return $this->passwordResetToken;
    }

    /**
     * Add a password reset token to the user.
     *
     * @param PasswordResetToken $password reset tokens
     * @return $this
     */
    public function addPasswordResetToken(PasswordResetToken $passwordResetToken): self
    {
        if (!$this->passwordResetTokens->contains($passwordResetToken)) {
            $this->passwordResetTokens[] = $passwordResetToken;
            $passwordResetToken->setCategory($this);
        }

        return $this;
    }

    /**
     * Remove a password reset token from the user.
     *
     * @param PasswordResetToken $passwordResetToken
     * @return $this
     */
    public function removePasswordResetToken(PasswordResetToken $passwordResetToken): self
    {
        if ($this->passwordResetTokens->contains($passwordResetToken)) {
            $this->passwordResetTokens->removeElement($passwordResetToken);
            if ($passwordResetToken->getCart() == $this) {
                $passwordResetToken->setCart(null);
            }
        }

        return $this;
    }
}