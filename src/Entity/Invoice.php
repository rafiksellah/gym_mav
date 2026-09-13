<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le numéro de facture est obligatoire.')]
    private ?string $number = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La date de facture est obligatoire.')]
    private ?\DateTimeImmutable $invoiceDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $purchaseOrder = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $paymentMethod = null;

    #[ORM\Column(length: 10)]
    private string $currency = 'DA';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, InvoiceLine>
     */
    #[ORM\OneToMany(targetEntity: InvoiceLine::class, mappedBy: 'invoice', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $lines;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->invoiceDate = new \DateTimeImmutable();
        $this->lines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getInvoiceDate(): ?\DateTimeImmutable
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(\DateTimeImmutable $invoiceDate): static
    {
        $this->invoiceDate = $invoiceDate;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

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

    public function getPurchaseOrder(): ?string
    {
        return $this->purchaseOrder;
    }

    public function setPurchaseOrder(?string $purchaseOrder): static
    {
        $this->purchaseOrder = $purchaseOrder;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, InvoiceLine>
     */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    public function addLine(InvoiceLine $line): static
    {
        if (!$this->lines->contains($line)) {
            $this->lines->add($line);
            $line->setInvoice($this);
        }

        return $this;
    }

    public function removeLine(InvoiceLine $line): static
    {
        if ($this->lines->removeElement($line)) {
            if ($line->getInvoice() === $this) {
                $line->setInvoice(null);
            }
        }

        return $this;
    }

    /**
     * Renumbers line positions sequentially, respecting current collection order.
     */
    public function reorderLines(): void
    {
        $criteria = Criteria::create()->orderBy(['position' => Criteria::ASC]);
        $ordered = $this->lines->matching($criteria);

        $position = 0;
        foreach ($ordered as $line) {
            $line->setPosition($position++);
        }
    }

    /**
     * Sum of each line's gross amount (quantity x unit price) before discount.
     */
    public function getSubtotalHt(): float
    {
        $sum = 0.0;
        foreach ($this->lines as $line) {
            $sum += $line->getGrossHt();
        }

        return round($sum, 2);
    }

    public function getTotalDiscount(): float
    {
        $sum = 0.0;
        foreach ($this->lines as $line) {
            $sum += $line->getDiscountAmount();
        }

        return round($sum, 2);
    }

    /**
     * Total HT after per-line discounts.
     */
    public function getTotalHt(): float
    {
        return round($this->getSubtotalHt() - $this->getTotalDiscount(), 2);
    }

    public function getTotalVat(): float
    {
        $sum = 0.0;
        foreach ($this->lines as $line) {
            $sum += $line->getTotalVat();
        }

        return round($sum, 2);
    }

    public function getTotalTtc(): float
    {
        return round($this->getTotalHt() + $this->getTotalVat(), 2);
    }

    /**
     * @return array<string, array{rate: float, base: float, amount: float}> VAT breakdown grouped by rate
     */
    public function getVatBreakdown(): array
    {
        $breakdown = [];
        foreach ($this->lines as $line) {
            $rate = (float) $line->getVatRate();
            $key = number_format($rate, 2);
            if (!isset($breakdown[$key])) {
                $breakdown[$key] = ['rate' => $rate, 'base' => 0.0, 'amount' => 0.0];
            }
            $breakdown[$key]['base'] += $line->getTotalHt();
            $breakdown[$key]['amount'] += $line->getTotalVat();
        }

        foreach ($breakdown as $key => $row) {
            $breakdown[$key]['base'] = round($row['base'], 2);
            $breakdown[$key]['amount'] = round($row['amount'], 2);
        }

        return $breakdown;
    }
}
