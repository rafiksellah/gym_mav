<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/clients')]
class ClientController extends AbstractController
{
    #[Route('', name: 'admin_client_index', methods: ['GET'])]
    public function index(Request $request, ClientRepository $clientRepository): Response
    {
        $search = $request->query->get('q');

        return $this->render('admin/client/index.html.twig', [
            'clients' => $clientRepository->search($search),
            'search' => $search,
        ]);
    }

    #[Route('/nouveau', name: 'admin_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($client);
            $em->flush();

            $this->addFlash('success', 'Client créé avec succès.');

            return $this->redirectToRoute('admin_client_index');
        }

        return $this->render('admin/client/form.html.twig', [
            'form' => $form,
            'client' => $client,
            'isNew' => true,
        ]);
    }

    #[Route('/{id}/edition', name: 'admin_client_edit', methods: ['GET', 'POST'])]
    public function edit(Client $client, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Client modifié avec succès.');

            return $this->redirectToRoute('admin_client_index');
        }

        return $this->render('admin/client/form.html.twig', [
            'form' => $form,
            'client' => $client,
            'isNew' => false,
        ]);
    }

    #[Route('/{id}/suppression', name: 'admin_client_delete', methods: ['POST'])]
    public function delete(Client $client, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete-client-'.$client->getId(), $request->request->get('_token'))) {
            if (!$client->getInvoices()->isEmpty()) {
                $this->addFlash('error', 'Impossible de supprimer un client ayant des factures.');

                return $this->redirectToRoute('admin_client_index');
            }

            $em->remove($client);
            $em->flush();

            $this->addFlash('success', 'Client supprimé.');
        }

        return $this->redirectToRoute('admin_client_index');
    }
}
