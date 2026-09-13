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
            'scriptFontPath' => $this->scriptFontAsDataUri(),
            'icon' => $this->iconsMap(),
            'headerPanelDataUri' => $this->assetAsDataUri('icons/header-panel.png'),
            'footerRibbonDataUri' => $this->assetAsDataUri('icons/footer-ribbon.png'),
            'amountInWords' => $this->numberToWords->convertAmount(
                $invoice->getTotalTtc(),
                $this->numberToWords->currencyLabel($invoice->getCurrency()),
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

    private function scriptFontAsDataUri(): ?string
    {
        $path = dirname($this->uploadsDirectory).'/assets/fonts/Sacramento-Regular.ttf';
        if (!is_file($path)) {
            return null;
        }

        return sprintf('data:font/ttf;base64,%s', base64_encode(file_get_contents($path)));
    }

    private function assetAsDataUri(string $relativePath): ?string
    {
        $path = dirname($this->uploadsDirectory).'/assets/'.ltrim($relativePath, '/');
        if (!is_file($path)) {
            return null;
        }

        return sprintf('data:image/png;base64,%s', base64_encode(file_get_contents($path)));
    }

    /**
     * @return array<string, ?string>
     */
    private function iconsMap(): array
    {
        $names = ['pin', 'phone', 'envelope', 'globe', 'calendar', 'building'];
        $colors = ['navy', 'white', 'gray'];

        $map = [];
        foreach ($names as $name) {
            foreach ($colors as $color) {
                $map[$name.'_'.$color] = $this->assetAsDataUri("icons/{$name}-{$color}.png");
            }
        }

        return $map;
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
}
