<?php

namespace App\Service;

use App\Entity\CompanySettings;
use App\Entity\Invoice;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class InvoicePdfGenerator
{
    public function __construct(
        private readonly Environment $twig,
        private readonly NumberToFrenchWordsConverter $numberToWords,
        private readonly string $uploadsDirectory,
    ) {
    }

    public function render(Invoice $invoice, CompanySettings $settings): string
    {
        $html = $this->twig->render('admin/invoice/pdf.html.twig', [
            'invoice' => $invoice,
            'settings' => $settings,
            'logoDataUri' => $this->logoAsDataUri($settings),
            'amountInWords' => $this->numberToWords->convertAmount(
                $invoice->getTotalTtc(),
                $this->currencyLabel($invoice->getCurrency()),
            ),
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function logoAsDataUri(CompanySettings $settings): ?string
    {
        $filename = $settings->getLogoFilename();
        if (!$filename) {
            return null;
        }

        $path = rtrim($this->uploadsDirectory, '/').'/company/'.$filename;
        if (!is_file($path)) {
            return null;
        }

        $mimeType = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/jpeg',
        };

        return sprintf('data:%s;base64,%s', $mimeType, base64_encode(file_get_contents($path)));
    }

    private function currencyLabel(string $currency): string
    {
        return match (strtoupper($currency)) {
            'DA', 'DZD' => 'dinar',
            'EUR', '€' => 'euro',
            'USD', '$' => 'dollar',
            default => $currency,
        };
    }
}
