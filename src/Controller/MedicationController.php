<?php

namespace App\Controller;

use App\Entity\Medication;
use App\Form\MedicationType;
use App\Repository\MedicationRepository;
use App\Service\DispenseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/medication')]
final class MedicationController extends AbstractController
{
    #[Route(name: 'app_medication_index', methods: ['GET'])]
    public function index(MedicationRepository $medicationRepository): Response
    {
        return $this->render('medication/index.html.twig', [
            'medications' => $medicationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_medication_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $medication = new Medication();
        $form = $this->createForm(MedicationType::class, $medication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($medication);
            $entityManager->flush();

            return $this->redirectToRoute('app_medication_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('medication/new.html.twig', [
            'medication' => $medication,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_medication_show', methods: ['GET'])]
    public function show(Medication $medication, DispenseService $dispenseService): Response
    {
        return $this->render('medication/show.html.twig', [
            'medication' => $medication,
            'type' => $medication->getMedicationTypes()[0] ?? null,
            'dispenses' => $dispenseService->getByMedication($medication)
        ]);
    }

    #[Route('/{id}/edit', name: 'app_medication_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Medication $medication, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MedicationType::class, $medication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_medication_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('medication/edit.html.twig', [
            'medication' => $medication,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_medication_delete', methods: ['POST'])]
    public function delete(Request $request, Medication $medication, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$medication->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($medication);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_medication_index', [], Response::HTTP_SEE_OTHER);
    }
}
