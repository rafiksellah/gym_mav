<?php

namespace App\Controller\Admin;

use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Form\InvoiceType;
use App\Repository\CompanySettingsRepository;
use App\Repository\InvoiceRepository;
use App\Service\InvoiceExcelExporter;
use App\Service\InvoiceNumberGenerator;
use App\Service\InvoicePdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/factures')]
class InvoiceController extends AbstractController
{
    #[Route('', name: 'admin_invoice_index', methods: ['GET'])]
    public function index(Request $request, InvoiceRepository $invoiceRepository): Response
    {
        $search = $request->query->get('q');

        return $this->render('admin/invoice/index.html.twig', [
            'invoices' => $invoiceRepository->search($search),
            'search' => $search,
        ]);
    }

    #[Route('/nouvelle', name: 'admin_invoice_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        CompanySettingsRepository $settingsRepository,
        InvoiceNumberGenerator $numberGenerator,
    ): Response {
        $settings = $settingsRepository->getOrCreate();

        $invoice = new Invoice();
        $invoice->setCurrency($settings->getDefaultCurrency());
        $invoice->setNumber($numberGenerator->peekNext($settings));

        $firstLine = new InvoiceLine();
        $firstLine->setVatRate($settings->getDefaultVatRate());
        $invoice->addLine($firstLine);

        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->normalizeLines($invoice);
            $numberGenerator->generateNext($settings);

            $em->persist($invoice);
            $em->flush();

            $this->addFlash('success', 'Facture créée avec succès.');

            return $this->redirectToRoute('admin_invoice_show', ['id' => $invoice->getId()]);
        }

        return $this->render('admin/invoice/form.html.twig', [
            'form' => $form,
            'invoice' => $invoice,
            'isNew' => true,
            'settings' => $settings,
        ]);
    }

    #[Route('/{id}/edition', name: 'admin_invoice_edit', methods: ['GET', 'POST'])]
    public function edit(
        Invoice $invoice,
        Request $request,
        EntityManagerInterface $em,
        CompanySettingsRepository $settingsRepository,
    ): Response {
        $settings = $settingsRepository->getOrCreate();

        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->normalizeLines($invoice);
            $em->flush();

            $this->addFlash('success', 'Facture modifiée avec succès.');

            return $this->redirectToRoute('admin_invoice_show', ['id' => $invoice->getId()]);
        }

        return $this->render('admin/invoice/form.html.twig', [
            'form' => $form,
            'invoice' => $invoice,
            'isNew' => false,
            'settings' => $settings,
        ]);
    }

    #[Route('/{id}', name: 'admin_invoice_show', methods: ['GET'])]
    public function show(Invoice $invoice, CompanySettingsRepository $settingsRepository): Response
    {
        return $this->render('admin/invoice/show.html.twig', [
            'invoice' => $invoice,
            'settings' => $settingsRepository->getOrCreate(),
        ]);
    }

    #[Route('/{id}/suppression', name: 'admin_invoice_delete', methods: ['POST'])]
    public function delete(Invoice $invoice, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete-invoice-'.$invoice->getId(), $request->request->get('_token'))) {
            $em->remove($invoice);
            $em->flush();

            $this->addFlash('success', 'Facture supprimée.');
        }

        return $this->redirectToRoute('admin_invoice_index');
    }

    #[Route('/{id}/pdf', name: 'admin_invoice_pdf', methods: ['GET'])]
    public function pdf(
        Invoice $invoice,
        Request $request,
        InvoicePdfGenerator $pdfGenerator,
        CompanySettingsRepository $settingsRepository,
    ): Response {
        $pdfContent = $pdfGenerator->render($invoice, $settingsRepository->getOrCreate());

        $filename = sprintf('facture-%s.pdf', str_replace('/', '-', $invoice->getNumber()));
        $disposition = 'inline' === $request->query->get('mode') ? 'inline' : 'attachment';

        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('%s; filename="%s"', $disposition, $filename));

        return $response;
    }

    #[Route('/{id}/excel', name: 'admin_invoice_excel', methods: ['GET'])]
    public function excel(
        Invoice $invoice,
        InvoiceExcelExporter $excelExporter,
        CompanySettingsRepository $settingsRepository,
    ): Response {
        $spreadsheet = $excelExporter->build($invoice, $settingsRepository->getOrCreate());
        $writer = new Xlsx($spreadsheet);

        $filename = sprintf('facture-%s.xlsx', str_replace('/', '-', $invoice->getNumber()));

        $response = new StreamedResponse(static function () use ($writer) {
            $writer->save('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));

        return $response;
    }

    /**
     * Ensures each line has a sequential position matching its order in the
     * submitted collection, so re-ordering via the up/down controls sticks.
     */
    private function normalizeLines(Invoice $invoice): void
    {
        $position = 0;
        foreach ($invoice->getLines() as $line) {
            $line->setPosition($position++);
        }
    }
}
