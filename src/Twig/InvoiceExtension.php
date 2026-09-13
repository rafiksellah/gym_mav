<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class InvoiceExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('invoice_money', $this->formatMoney(...)),
            new TwigFilter('invoice_qty', $this->formatQuantity(...)),
        ];
    }

    public function formatMoney(float|string $amount): string
    {
        return number_format((float) $amount, 2, ',', ' ');
    }

    public function formatQuantity(float|string $quantity): string
    {
        $quantity = (float) $quantity;

        if (abs($quantity - round($quantity)) < 0.0001) {
            return number_format($quantity, 0, ',', ' ');
        }

        return number_format($quantity, 3, ',', ' ');
    }
}
