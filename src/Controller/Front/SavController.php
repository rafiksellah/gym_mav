<?php

namespace App\Controller\Front;

use App\Entity\SavRequest;
use App\Form\SavRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SavController extends AbstractController
{
    #[Route('/service-apres-vente', name: 'front_sav')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $savRequest = new SavRequest();
        $form = $this->createForm(SavRequestType::class, $savRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($savRequest);
            $entityManager->flush();

            $this->addFlash('success', 'Votre demande d\'intervention a bien été envoyée. Notre équipe SAV vous recontactera rapidement.');

            return $this->redirectToRoute('front_sav');
        }

        return $this->render('front/sav/index.html.twig', [
            'form' => $form,
        ]);
    }
}
