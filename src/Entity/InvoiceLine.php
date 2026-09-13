<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class InvoiceLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'lines')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Invoice $invoice = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La désignation est obligatoire.')]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 3)]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'La quantité doit être positive.')]
    private string $quantity = '1.000';

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $unit = 'unité';

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 2)]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero(message: 'Le prix unitaire doit être positif ou nul.')]
    private string $unitPriceHt = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 100)]
    private string $discountPercent = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 100)]
    private string $vatRate = '19.00';

    #[ORM\Column]
    private int $position = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getQuantity(): string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    public function getUnitPriceHt(): string
    {
        return $this->unitPriceHt;
    }

    public function setUnitPriceHt(string $unitPriceHt): static
    {
        $this->unitPriceHt = $unitPriceHt;

        return $this;
    }

    public function getDiscountPercent(): string
    {
        return $this->discountPercent;
    }

    public function setDiscountPercent(string $discountPercent): static
    {
        $this->discountPercent = $discountPercent;

        return $this;
    }

    public function getVatRate(): string
    {
        return $this->vatRate;
    }

    public function setVatRate(string $vatRate): static
    {
        $this->vatRate = $vatRate;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Total HT before discount (quantity x unit price).
     */
    public function getGrossHt(): float
    {
        return round((float) $this->quantity * (float) $this->unitPriceHt, 2);
    }

    public function getDiscountAmount(): float
    {
        return round($this->getGrossHt() * ((float) $this->discountPercent / 100), 2);
    }

    /**
     * Total HT after discount.
     */
    public function getTotalHt(): float
    {
        return round($this->getGrossHt() - $this->getDiscountAmount(), 2);
    }

    public function getTotalVat(): float
    {
        return round($this->getTotalHt() * ((float) $this->vatRate / 100), 2);
    }

    public function getTotalTtc(): float
    {
        return round($this->getTotalHt() + $this->getTotalVat(), 2);
    }
}
