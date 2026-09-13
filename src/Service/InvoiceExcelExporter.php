<?php

namespace App\Service;

use App\Entity\CompanySettings;
use App\Entity\Invoice;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InvoiceExcelExporter
{
    private const NAVY = '1B3A63';
    private const LIGHT_GRAY = 'F2F4F6';

    public function build(Invoice $invoice, CompanySettings $settings): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Facture '.preg_replace('/[\\\\\/\?\*\[\]:]/', '-', $invoice->getNumber()));

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(18);

        $row = 1;

        $sheet->setCellValue("A{$row}", $settings->getName());
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16)->setColor(new Color(self::NAVY));

        $sheet->setCellValue("E{$row}", 'FACTURE');
        $sheet->mergeCells("E{$row}:F{$row}");
        $sheet->getStyle("E{$row}")->getFont()->setBold(true)->setSize(16)->setColor(new Color(self::NAVY));
        $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        ++$row;

        if ($settings->getAddress()) {
            $sheet->setCellValue("A{$row}", $settings->getAddress());
            $sheet->mergeCells("A{$row}:D{$row}");
            ++$row;
        }
        foreach (array_filter([$settings->getPhone(), $settings->getEmail(), $settings->getWebsite()]) as $line) {
            $sheet->setCellValue("A{$row}", $line);
            $sheet->mergeCells("A{$row}:D{$row}");
            ++$row;
        }

        $sheet->setCellValue("E{$row}", 'N°');
        $sheet->setCellValue("F{$row}", $invoice->getNumber());
        ++$row;
        $sheet->setCellValue("E{$row}", "Date d'émission");
        $sheet->setCellValue("F{$row}", $invoice->getInvoiceDate()?->format('d/m/Y'));
        ++$row;

        $row += 1;

        $sheet->setCellValue("A{$row}", 'CLIENT');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setColor(new Color(self::NAVY));
        ++$row;
        $sheet->setCellValue("A{$row}", $invoice->getClient()->getFullName());
        $sheet->mergeCells("A{$row}:D{$row}");
        ++$row;
        $clientLines = array_filter([
            $invoice->getClient()->getNif() ? 'NIF : '.$invoice->getClient()->getNif() : null,
            $invoice->getClient()->getAi() ? 'Article : '.$invoice->getClient()->getAi() : null,
            $invoice->getClient()->getNis() ? 'NIS : '.$invoice->getClient()->getNis() : null,
        ]);
        foreach ($clientLines as $line) {
            $sheet->setCellValue("A{$row}", $line);
            $sheet->mergeCells("A{$row}:D{$row}");
            ++$row;
        }

        $row += 1;
        $headerRow = $row;
        $headers = ['N°', 'Désignation', 'Qté', 'Unité', 'P.U HT', 'Montant'];
        foreach ($headers as $col => $label) {
            $sheet->getCell([$col + 1, $headerRow])->setValue($label);
        }
        $sheet->getStyle("A{$headerRow}:F{$headerRow}")->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
        $sheet->getStyle("A{$headerRow}:F{$headerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::NAVY);
        $sheet->getStyle("A{$headerRow}:F{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        ++$row;

        $lineStart = $row;
        foreach ($invoice->getLines() as $index => $line) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $line->getDescription());
            $sheet->setCellValue("C{$row}", (float) $line->getQuantity());
            $sheet->setCellValue("D{$row}", $line->getUnit());
            $sheet->setCellValue("E{$row}", (float) $line->getUnitPriceHt());
            $sheet->setCellValue("F{$row}", $line->getLineTotal());

            if (0 === $index % 2) {
                $sheet->getStyle("A{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::LIGHT_GRAY);
            }
            ++$row;
        }
        $lineEnd = $row - 1;

        if ($lineEnd >= $lineStart) {
            $sheet->getStyle("E{$lineStart}:E{$lineEnd}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("F{$lineStart}:F{$lineEnd}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("A{$lineStart}:F{$lineEnd}")
                ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('DDDDDD');
        }

        $row += 1;
        $sheet->setCellValue("E{$row}", 'Total HT');
        $sheet->getStyle("E{$row}")->getFont()->setBold(true);
        $sheet->setCellValue("F{$row}", $invoice->getTotalHt());
        $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        ++$row;
        $sheet->setCellValue("E{$row}", 'TVA 19%');
        $sheet->getStyle("E{$row}")->getFont()->setBold(true);
        $sheet->setCellValue("F{$row}", $invoice->getTotalVat());
        $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        ++$row;
        $sheet->setCellValue("E{$row}", 'TOTAL TTC');
        $sheet->setCellValue("F{$row}", $invoice->getTotalTtc());
        $sheet->getStyle("E{$row}:F{$row}")->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFF'));
        $sheet->getStyle("E{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::NAVY);
        $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $row += 2;

        $sheet->setCellValue("A{$row}", 'Arrêtée la somme de :');
        $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
        ++$row;

        if ($settings->getPaymentTerms()) {
            $row += 1;
            $sheet->setCellValue("A{$row}", $settings->getPaymentTerms());
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->getStyle("A{$row}")->getAlignment()->setWrapText(true);
        }

        return $spreadsheet;
    }

    public function write(Spreadsheet $spreadsheet, string $path): void
    {
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
    }
}
