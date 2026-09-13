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
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(18);

        $row = 1;

        $sheet->setCellValue("A{$row}", $settings->getName());
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16)->setColor(new Color(self::NAVY));

        $sheet->setCellValue("D{$row}", 'FACTURE');
        $sheet->mergeCells("D{$row}:E{$row}");
        $sheet->getStyle("D{$row}")->getFont()->setBold(true)->setSize(16)->setColor(new Color(self::NAVY));
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        ++$row;

        if ($settings->getAddress()) {
            $sheet->setCellValue("A{$row}", $settings->getAddress());
            $sheet->mergeCells("A{$row}:C{$row}");
            ++$row;
        }
        foreach (array_filter([$settings->getPhone(), $settings->getEmail(), $settings->getWebsite()]) as $line) {
            $sheet->setCellValue("A{$row}", $line);
            $sheet->mergeCells("A{$row}:C{$row}");
            ++$row;
        }

        $sheet->setCellValue("D{$row}", 'N°');
        $sheet->setCellValue("E{$row}", $invoice->getNumber());
        ++$row;
        $sheet->setCellValue("D{$row}", "Date d'émission");
        $sheet->setCellValue("E{$row}", $invoice->getInvoiceDate()?->format('d/m/Y'));
        ++$row;

        $row += 1;

        $sheet->setCellValue("A{$row}", 'CLIENT');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setColor(new Color(self::NAVY));
        ++$row;
        $sheet->setCellValue("A{$row}", $invoice->getClient()->getFullName());
        $sheet->mergeCells("A{$row}:C{$row}");
        ++$row;
        $clientLines = array_filter([
            $invoice->getClient()->getNif() ? 'NIF : '.$invoice->getClient()->getNif() : null,
            $invoice->getClient()->getAi() ? 'Article : '.$invoice->getClient()->getAi() : null,
            $invoice->getClient()->getNis() ? 'NIS : '.$invoice->getClient()->getNis() : null,
        ]);
        foreach ($clientLines as $line) {
            $sheet->setCellValue("A{$row}", $line);
            $sheet->mergeCells("A{$row}:C{$row}");
            ++$row;
        }

        $row += 1;
        $headerRow = $row;
        $headers = ['N°', 'Désignation', 'Qté', 'P.U HT', 'Montant'];
        foreach ($headers as $col => $label) {
            $sheet->getCell([$col + 1, $headerRow])->setValue($label);
        }
        $sheet->getStyle("A{$headerRow}:E{$headerRow}")->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
        $sheet->getStyle("A{$headerRow}:E{$headerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::NAVY);
        $sheet->getStyle("A{$headerRow}:E{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        ++$row;

        $lineStart = $row;
        foreach ($invoice->getLines() as $index => $line) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $line->getDescription());
            $sheet->setCellValue("C{$row}", (float) $line->getQuantity());
            $sheet->setCellValue("D{$row}", (float) $line->getUnitPriceHt());
            $sheet->setCellValue("E{$row}", $line->getLineTotal());

            if (0 === $index % 2) {
                $sheet->getStyle("A{$row}:E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::LIGHT_GRAY);
            }
            ++$row;
        }
        $lineEnd = $row - 1;

        if ($lineEnd >= $lineStart) {
            $sheet->getStyle("D{$lineStart}:D{$lineEnd}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("E{$lineStart}:E{$lineEnd}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("A{$lineStart}:E{$lineEnd}")
                ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('DDDDDD');
        }

        $row += 1;
        $sheet->setCellValue("D{$row}", 'Total HT');
        $sheet->getStyle("D{$row}")->getFont()->setBold(true);
        $sheet->setCellValue("E{$row}", $invoice->getTotalHt());
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        ++$row;
        $sheet->setCellValue("D{$row}", 'TVA 19%');
        $sheet->getStyle("D{$row}")->getFont()->setBold(true);
        $sheet->setCellValue("E{$row}", $invoice->getTotalVat());
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        ++$row;
        $sheet->setCellValue("D{$row}", 'TOTAL TTC');
        $sheet->setCellValue("E{$row}", $invoice->getTotalTtc());
        $sheet->getStyle("D{$row}:E{$row}")->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFF'));
        $sheet->getStyle("D{$row}:E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::NAVY);
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $row += 2;

        $sheet->setCellValue("A{$row}", 'Arrêtée la somme de :');
        $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
        ++$row;

        if ($settings->getPaymentTerms()) {
            $row += 1;
            $sheet->setCellValue("A{$row}", $settings->getPaymentTerms());
            $sheet->mergeCells("A{$row}:E{$row}");
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
