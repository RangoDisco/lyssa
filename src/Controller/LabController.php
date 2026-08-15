<?php

namespace App\Controller;

use App\Entity\Lab;
use App\Form\LabType;
use App\Repository\LabRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lab')]
final class LabController extends AbstractController
{
    #[Route(name: 'app_lab_index', methods: ['GET'])]
    public function index(LabRepository $labRepository): Response
    {
        return $this->render('lab/index.html.twig', [
            'labs' => $labRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_lab_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lab = new Lab();
        $form = $this->createForm(LabType::class, $lab);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lab);
            $entityManager->flush();

            return $this->redirectToRoute('app_lab_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lab/new.html.twig', [
            'lab' => $lab,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_lab_show', methods: ['GET'])]
    public function show(Lab $lab): Response
    {
        return $this->render('lab/show.html.twig', [
            'lab' => $lab,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_lab_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Lab $lab, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LabType::class, $lab);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_lab_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lab/edit.html.twig', [
            'lab' => $lab,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_lab_delete', methods: ['POST'])]
    public function delete(Request $request, Lab $lab, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lab->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($lab);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_lab_index', [], Response::HTTP_SEE_OTHER);
    }
}
