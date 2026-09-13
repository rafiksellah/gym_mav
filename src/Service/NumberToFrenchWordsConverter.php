<?php

namespace App\Service;

/**
 * Converts a monetary amount into French words, as used on the printed
 * invoice ("Arrêtée la somme de : Huit cent quatre-vingt-treize mille
 * trente cinq dinar et 50 cts.").
 */
class NumberToFrenchWordsConverter
{
    private const UNITS = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'];
    private const TEENS = ['dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
    private const TENS = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];

    public function currencyLabel(string $currency): string
    {
        return match (strtoupper($currency)) {
            'DA', 'DZD' => 'dinar',
            'EUR', '€' => 'euro',
            'USD', '$' => 'dollar',
            default => $currency,
        };
    }

    public function convertAmount(float $amount, string $currencyName = 'dinar', string $centsLabel = 'cts'): string
    {
        $amount = round($amount, 2);
        $integerPart = (int) floor($amount);
        $centimes = (int) round(($amount - $integerPart) * 100);

        $words = 0 === $integerPart ? 'zéro' : $this->convertInteger($integerPart);
        $words = ucfirst($words);

        $result = sprintf('%s %s', $words, $currencyName);
        if ($centimes > 0) {
            $result .= sprintf(' et %02d %s', $centimes, $centsLabel);
        }

        return $result;
    }

    public function convertInteger(int $number): string
    {
        if (0 === $number) {
            return 'zéro';
        }

        if ($number < 0) {
            return 'moins '.$this->convertInteger(-$number);
        }

        if ($number < 1_000_000_000) {
            $billions = 0;
        } else {
            $billions = intdiv($number, 1_000_000_000);
            $number %= 1_000_000_000;
        }

        $millions = intdiv($number, 1_000_000);
        $number %= 1_000_000;

        $thousands = intdiv($number, 1_000);
        $number %= 1_000;

        $parts = [];

        if ($billions > 0) {
            $parts[] = 1 === $billions ? 'un milliard' : $this->convertUnderThousand($billions).' milliards';
        }

        if ($millions > 0) {
            $parts[] = 1 === $millions ? 'un million' : $this->convertUnderThousand($millions).' millions';
        }

        if ($thousands > 0) {
            $parts[] = 1 === $thousands ? 'mille' : $this->convertUnderThousand($thousands).' mille';
        }

        if ($number > 0) {
            $parts[] = $this->convertUnderThousand($number);
        }

        return implode(' ', $parts);
    }

    private function convertUnderThousand(int $number): string
    {
        if ($number < 10) {
            return self::UNITS[$number];
        }

        if ($number < 20) {
            return self::TEENS[$number - 10];
        }

        if ($number < 100) {
            return $this->convertTens($number);
        }

        $hundreds = intdiv($number, 100);
        $remainder = $number % 100;

        $prefix = 1 === $hundreds ? 'cent' : self::UNITS[$hundreds].' cent';
        if (0 === $remainder && $hundreds > 1) {
            $prefix .= 's';
        }

        if (0 === $remainder) {
            return $prefix;
        }

        return $prefix.' '.$this->convertTens($remainder);
    }

    private function convertTens(int $number): string
    {
        if ($number < 20) {
            return $number < 10 ? self::UNITS[$number] : self::TEENS[$number - 10];
        }

        $ten = intdiv($number, 10);
        $unit = $number % 10;

        // soixante-dix (70-79) and quatre-vingt-dix (90-99) are built from the
        // 60 / 80 base plus a teen.
        if (7 === $ten || 9 === $ten) {
            $base = self::TENS[$ten - 1];

            return 0 === $unit ? $base.'-dix' : $base.'-'.self::TEENS[$unit];
        }

        $base = self::TENS[$ten];

        if (0 === $unit) {
            return 8 === $ten ? $base.'s' : $base;
        }

        if (1 === $unit && $ten < 8) {
            return $base.' et un';
        }

        return $base.'-'.self::UNITS[$unit];
    }
}
