<?php

namespace App\Entity;

use App\Enum\CryptoType;
use App\Enum\RequestType;
use App\Repository\RequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequestRepository::class)]
#[ORM\Table(name: '`requests`')]
#[ORM\HasLifecycleCallbacks]
class Request
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: RequestType::class)]
    private ?RequestType $type = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 8)]
    private ?string $amount = null;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: CryptoType::class)]
    private ?CryptoType $cryptoType = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $publicAddress = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isValidated = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $validatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?RequestType
    {
        return $this->type;
    }

    public function setType(RequestType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCryptoType(): ?CryptoType
    {
        return $this->cryptoType;
    }

    public function setCryptoType(CryptoType $cryptoType): static
    {
        $this->cryptoType = $cryptoType;

        return $this;
    }

    public function getPublicAddress(): ?string
    {
        return $this->publicAddress;
    }

    public function setPublicAddress(?string $publicAddress): static
    {
        $this->publicAddress = $publicAddress;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isValidated(): bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): static
    {
        $this->isValidated = $isValidated;

        if ($isValidated && $this->validatedAt === null) {
            $this->validatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

