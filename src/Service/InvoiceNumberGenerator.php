<?php

namespace App\Service;

use App\Entity\CompanySettings;

/**
 * Builds sequential invoice numbers such as "57/SP/26" (sequence / prefix / year).
 */
class InvoiceNumberGenerator
{
    public function generateNext(CompanySettings $settings, ?\DateTimeImmutable $date = null): string
    {
        $sequence = $settings->incrementInvoiceSequence();

        return $this->format($sequence, $settings, $date);
    }

    /**
     * Previews the number that generateNext() would produce, without
     * consuming the sequence counter.
     */
    public function peekNext(CompanySettings $settings, ?\DateTimeImmutable $date = null): string
    {
        return $this->format($settings->getLastInvoiceSequence() + 1, $settings, $date);
    }

    private function format(int $sequence, CompanySettings $settings, ?\DateTimeImmutable $date = null): string
    {
        $date ??= new \DateTimeImmutable();
        $year = $date->format('y');
        $prefix = trim($settings->getInvoiceNumberPrefix());

        return $prefix
            ? sprintf('%d/%s/%s', $sequence, $prefix, $year)
            : sprintf('%d/%s', $sequence, $year);
    }
}
