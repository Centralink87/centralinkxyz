<?php

namespace App\Entity;

use App\Enum\CryptoType;
use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: '`transactions`')]
#[ORM\HasLifecycleCallbacks]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 8)]
    private ?string $entryPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 8, nullable: true)]
    private ?string $exitPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 8)]
    private ?string $amount = null;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: CryptoType::class)]
    private ?CryptoType $cryptoType = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $transactionDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isValidated = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $validatedAt = null;

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

    public function getEntryPrice(): ?string
    {
        return $this->entryPrice;
    }

    public function setEntryPrice(string $entryPrice): static
    {
        $this->entryPrice = $entryPrice;

        return $this;
    }

    public function getExitPrice(): ?string
    {
        return $this->exitPrice;
    }

    public function setExitPrice(?string $exitPrice): static
    {
        $this->exitPrice = $exitPrice;

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

    public function getTransactionDate(): ?\DateTimeImmutable
    {
        return $this->transactionDate;
    }

    public function setTransactionDate(\DateTimeImmutable $transactionDate): static
    {
        $this->transactionDate = $transactionDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
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

    /**
     * Calcule le profit/perte si exitPrice est défini
     */
    public function getProfitLoss(): ?string
    {
        if ($this->exitPrice === null || $this->entryPrice === null || $this->amount === null) {
            return null;
        }

        $entry = (float) $this->entryPrice;
        $exit = (float) $this->exitPrice;
        $amount = (float) $this->amount;

        return (string) (($exit - $entry) * $amount);
    }

    /**
     * Calcule le pourcentage de profit/perte
     */
    public function getProfitLossPercentage(): ?float
    {
        if ($this->exitPrice === null || $this->entryPrice === null || (float) $this->entryPrice === 0.0) {
            return null;
        }

        $entry = (float) $this->entryPrice;
        $exit = (float) $this->exitPrice;

        return (($exit - $entry) / $entry) * 100;
    }
}

