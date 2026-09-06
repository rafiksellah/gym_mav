<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use App\Entity\QuoteRequest;
use App\Entity\SavRequest;
use App\Repository\ContactMessageRepository;
use App\Repository\QuoteRequestRepository;
use App\Repository\SavRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/messages')]
class MessageController extends AbstractController
{
    #[Route('/contact', name: 'admin_message_contact_index', methods: ['GET'])]
    public function contactIndex(ContactMessageRepository $repository): Response
    {
        return $this->render('admin/message/contact_index.html.twig', [
            'messages' => $repository->findAllOrdered(),
        ]);
    }

    #[Route('/contact/{id}', name: 'admin_message_contact_show', methods: ['GET'])]
    public function contactShow(ContactMessage $message, EntityManagerInterface $em): Response
    {
        if (!$message->isRead()) {
            $message->setIsRead(true);
            $em->flush();
        }

        return $this->render('admin/message/contact_show.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/contact/{id}/suppression', name: 'admin_message_contact_delete', methods: ['POST'])]
    public function contactDelete(ContactMessage $message, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete-message-'.$message->getId(), $request->request->get('_token'))) {
            $em->remove($message);
            $em->flush();
            $this->addFlash('success', 'Message supprimé.');
        }

        return $this->redirectToRoute('admin_message_contact_index');
    }

    #[Route('/devis', name: 'admin_message_quote_index', methods: ['GET'])]
    public function quoteIndex(QuoteRequestRepository $repository): Response
    {
        return $this->render('admin/message/quote_index.html.twig', [
            'requests' => $repository->findAllOrdered(),
        ]);
    }

    #[Route('/devis/{id}', name: 'admin_message_quote_show', methods: ['GET'])]
    public function quoteShow(QuoteRequest $quoteRequest, EntityManagerInterface $em): Response
    {
        if (!$quoteRequest->isRead()) {
            $quoteRequest->setIsRead(true);
            $em->flush();
        }

        return $this->render('admin/message/quote_show.html.twig', [
            'quoteRequest' => $quoteRequest,
        ]);
    }

    #[Route('/devis/{id}/suppression', name: 'admin_message_quote_delete', methods: ['POST'])]
    public function quoteDelete(QuoteRequest $quoteRequest, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete-message-'.$quoteRequest->getId(), $request->request->get('_token'))) {
            $em->remove($quoteRequest);
            $em->flush();
            $this->addFlash('success', 'Demande de devis supprimée.');
        }

        return $this->redirectToRoute('admin_message_quote_index');
    }

    #[Route('/sav', name: 'admin_message_sav_index', methods: ['GET'])]
    public function savIndex(SavRequestRepository $repository): Response
    {
        return $this->render('admin/message/sav_index.html.twig', [
            'requests' => $repository->findAllOrdered(),
        ]);
    }

    #[Route('/sav/{id}', name: 'admin_message_sav_show', methods: ['GET'])]
    public function savShow(SavRequest $savRequest, EntityManagerInterface $em): Response
    {
        if (!$savRequest->isRead()) {
            $savRequest->setIsRead(true);
            $em->flush();
        }

        return $this->render('admin/message/sav_show.html.twig', [
            'savRequest' => $savRequest,
        ]);
    }

    #[Route('/sav/{id}/suppression', name: 'admin_message_sav_delete', methods: ['POST'])]
    public function savDelete(SavRequest $savRequest, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete-message-'.$savRequest->getId(), $request->request->get('_token'))) {
            $em->remove($savRequest);
            $em->flush();
            $this->addFlash('success', 'Demande SAV supprimée.');
        }

        return $this->redirectToRoute('admin_message_sav_index');
    }
}
