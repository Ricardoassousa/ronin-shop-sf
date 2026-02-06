<?php

namespace App\Entity;

use App\Entity\User;
use DateTime;

/**
 * PasswordResetToken
 */
class PasswordResetToken
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $token;

    /**
     * @var Datetime
     */
    private $createdAt;

    /**
     * @var Datetime
     */
    private $expiresAt;

    /**
     *
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->expiresAt = new DateTime('+1 hour');
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
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken(string $token): self
    {
        $this->token = $token;
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
    public function getExpiresAt(): Datetime
    {
        return $this->expiresAt;
    }

    /**
     * @param Datetime $expiresAt
     *
     * @return $this
     */
    public function setExpiresAt(Datetime $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

}