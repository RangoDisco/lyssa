<?php

namespace App\Controller;

use App\Entity\Dispense;
use App\Form\DispenseType;
use App\Repository\DispenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dispense')]
#[IsGranted("IS_AUTHENTICATED")]
final class DispenseController extends AbstractController
{
    #[Route(name: 'app_dispense_index', methods: ['GET'])]
    public function index(DispenseRepository $dispenseRepository): Response
    {
        return $this->render('dispense/index.html.twig', [
            'dispenses' => $dispenseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_dispense_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dispense = new Dispense();
        $form = $this->createForm(DispenseType::class, $dispense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($dispense);
            $entityManager->flush();

            return $this->redirectToRoute('app_dispense_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dispense/new.html.twig', [
            'dispense' => $dispense,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_dispense_show', methods: ['GET'])]
    #[IsGranted("DISPENSE_VIEW", subject: 'dispense')]
    public function show(Dispense $dispense): Response
    {
        return $this->render('dispense/show.html.twig', [
            'dispense' => $dispense,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_dispense_edit', methods: ['GET', 'POST'])]
    #[IsGranted("DISPENSE_EDIT", subject: 'dispense')]
    public function edit(Request $request, Dispense $dispense, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DispenseType::class, $dispense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_dispense_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dispense/edit.html.twig', [
            'dispense' => $dispense,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_dispense_delete', methods: ['POST'])]
    #[IsGranted("DISPENSE_DELETE", subject: 'dispense')]
    public function delete(Request $request, Dispense $dispense, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $dispense->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($dispense);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_dispense_index', [], Response::HTTP_SEE_OTHER);
    }
}
