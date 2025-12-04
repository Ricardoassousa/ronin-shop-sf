<?php

namespace App\Entity;

use App\Entity\CustomerProfile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

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

}