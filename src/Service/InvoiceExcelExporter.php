<?php

namespace App\Service;

use App\Entity\CompanySettings;
use App\Entity\Invoice;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
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
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(18);

        $row = 1;

        $sheet->setCellValue("A{$row}", $settings->getName());
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(self::NAVY));

        $sheet->setCellValue("F{$row}", 'FACTURE');
        $sheet->mergeCells("F{$row}:G{$row}");
        $sheet->getStyle("F{$row}")->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(self::NAVY));
        $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
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

        $sheet->setCellValue("F{$row}", 'N°');
        $sheet->setCellValue("G{$row}", $invoice->getNumber());
        ++$row;
        $sheet->setCellValue("F{$row}", "Date d'émission");
        $sheet->setCellValue("G{$row}", $invoice->getInvoiceDate()?->format('d/m/Y'));
        ++$row;
        if ($invoice->getDueDate()) {
            $sheet->setCellValue("F{$row}", "Date d'échéance");
            $sheet->setCellValue("G{$row}", $invoice->getDueDate()->format('d/m/Y'));
            ++$row;
        }

        $row += 1;

        $sheet->setCellValue("A{$row}", 'CLIENT');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(self::NAVY));
        ++$row;
        $sheet->setCellValue("A{$row}", $invoice->getClient()->getFullName());
        $sheet->mergeCells("A{$row}:D{$row}");
        ++$row;
        foreach (array_filter([$invoice->getClient()->getAddress(), $invoice->getClient()->getEmail(), $invoice->getClient()->getPhone()]) as $line) {
            $sheet->setCellValue("A{$row}", $line);
            $sheet->mergeCells("A{$row}:D{$row}");
            ++$row;
        }
        if ($invoice->getReference()) {
            $sheet->setCellValue("A{$row}", 'Référence : '.$invoice->getReference());
            $sheet->mergeCells("A{$row}:D{$row}");
            ++$row;
        }

        $row += 1;
        $headerRow = $row;
        $headers = ['N°', 'Désignation', 'Qté', 'Unité', 'P.U HT', 'TVA %', 'Total TTC'];
        foreach ($headers as $col => $label) {
            $sheet->getCell([$col + 1, $headerRow])->setValue($label);
        }
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::NAVY);
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        ++$row;

        $lineStart = $row;
        foreach ($invoice->getLines() as $index => $line) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $line->getDescription());
            $sheet->setCellValue("C{$row}", (float) $line->getQuantity());
            $sheet->setCellValue("D{$row}", $line->getUnit());
            $sheet->setCellValue("E{$row}", (float) $line->getUnitPriceHt());
            $sheet->setCellValue("F{$row}", (float) $line->getVatRate());
            $sheet->setCellValue("G{$row}", $line->getTotalTtc());

            if (0 === $index % 2) {
                $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::LIGHT_GRAY);
            }
            ++$row;
        }
        $lineEnd = $row - 1;

        if ($lineEnd >= $lineStart) {
            $sheet->getStyle("E{$lineStart}:E{$lineEnd}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("G{$lineStart}:G{$lineEnd}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("A{$lineStart}:G{$lineEnd}")
                ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('DDDDDD');
        }

        $row += 1;
        $totals = [
            ['Total HT', $invoice->getTotalHt()],
            ['Remise', $invoice->getTotalDiscount()],
            ['Total TVA', $invoice->getTotalVat()],
        ];
        foreach ($totals as [$label, $value]) {
            $sheet->setCellValue("F{$row}", $label);
            $sheet->getStyle("F{$row}")->getFont()->setBold(true);
            $sheet->setCellValue("G{$row}", $value);
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            ++$row;
        }
        $sheet->setCellValue("F{$row}", 'TOTAL TTC');
        $sheet->setCellValue("G{$row}", $invoice->getTotalTtc());
        $sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle("F{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::NAVY);
        $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $row += 2;

        $sheet->setCellValue("A{$row}", 'Arrêtée la somme de :');
        $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
        ++$row;
        $sheet->setCellValue("A{$row}", $invoice->getPaymentMethod() ? 'Mode de paiement : '.$invoice->getPaymentMethod() : '');
        $sheet->mergeCells("A{$row}:D{$row}");

        if ($settings->getPaymentTerms()) {
            $row += 2;
            $sheet->setCellValue("A{$row}", $settings->getPaymentTerms());
            $sheet->mergeCells("A{$row}:G{$row}");
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
