<?php

namespace App\Entity;

use App\Repository\CompanySettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompanySettingsRepository::class)]
class CompanySettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "Le nom de l'entreprise est obligatoire.")]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tagline = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slogan = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoFilename = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email(message: 'Cet email n\'est pas valide.')]
    private ?string $email = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nif = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nis = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $rc = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ai = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $rib = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bankInfo = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $capitalSocial = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $paymentTerms = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $legalMentions = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 100)]
    private string $defaultVatRate = '19.00';

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    private string $defaultCurrency = 'DA';

    #[ORM\Column(length: 20)]
    private string $invoiceNumberPrefix = '';

    #[ORM\Column]
    private int $lastInvoiceSequence = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getTagline(): ?string
    {
        return $this->tagline;
    }

    public function setTagline(?string $tagline): static
    {
        $this->tagline = $tagline;

        return $this;
    }

    public function getSlogan(): ?string
    {
        return $this->slogan;
    }

    public function setSlogan(?string $slogan): static
    {
        $this->slogan = $slogan;

        return $this;
    }

    public function getLogoFilename(): ?string
    {
        return $this->logoFilename;
    }

    public function setLogoFilename(?string $logoFilename): static
    {
        $this->logoFilename = $logoFilename;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getNif(): ?string
    {
        return $this->nif;
    }

    public function setNif(?string $nif): static
    {
        $this->nif = $nif;

        return $this;
    }

    public function getNis(): ?string
    {
        return $this->nis;
    }

    public function setNis(?string $nis): static
    {
        $this->nis = $nis;

        return $this;
    }

    public function getRc(): ?string
    {
        return $this->rc;
    }

    public function setRc(?string $rc): static
    {
        $this->rc = $rc;

        return $this;
    }

    public function getAi(): ?string
    {
        return $this->ai;
    }

    public function setAi(?string $ai): static
    {
        $this->ai = $ai;

        return $this;
    }

    public function getRib(): ?string
    {
        return $this->rib;
    }

    public function setRib(?string $rib): static
    {
        $this->rib = $rib;

        return $this;
    }

    public function getBankInfo(): ?string
    {
        return $this->bankInfo;
    }

    public function setBankInfo(?string $bankInfo): static
    {
        $this->bankInfo = $bankInfo;

        return $this;
    }

    public function getCapitalSocial(): ?string
    {
        return $this->capitalSocial;
    }

    public function setCapitalSocial(?string $capitalSocial): static
    {
        $this->capitalSocial = $capitalSocial;

        return $this;
    }

    public function getPaymentTerms(): ?string
    {
        return $this->paymentTerms;
    }

    public function setPaymentTerms(?string $paymentTerms): static
    {
        $this->paymentTerms = $paymentTerms;

        return $this;
    }

    public function getLegalMentions(): ?string
    {
        return $this->legalMentions;
    }

    public function setLegalMentions(?string $legalMentions): static
    {
        $this->legalMentions = $legalMentions;

        return $this;
    }

    public function getDefaultVatRate(): string
    {
        return $this->defaultVatRate;
    }

    public function setDefaultVatRate(string $defaultVatRate): static
    {
        $this->defaultVatRate = $defaultVatRate;

        return $this;
    }

    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }

    public function setDefaultCurrency(string $defaultCurrency): static
    {
        $this->defaultCurrency = $defaultCurrency;

        return $this;
    }

    public function getInvoiceNumberPrefix(): string
    {
        return $this->invoiceNumberPrefix;
    }

    public function setInvoiceNumberPrefix(string $invoiceNumberPrefix): static
    {
        $this->invoiceNumberPrefix = $invoiceNumberPrefix;

        return $this;
    }

    public function getLastInvoiceSequence(): int
    {
        return $this->lastInvoiceSequence;
    }

    public function setLastInvoiceSequence(int $lastInvoiceSequence): static
    {
        $this->lastInvoiceSequence = $lastInvoiceSequence;

        return $this;
    }

    public function incrementInvoiceSequence(): int
    {
        $this->lastInvoiceSequence++;

        return $this->lastInvoiceSequence;
    }
}
