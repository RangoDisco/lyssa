<?php

namespace App\Controller;

use App\Entity\Substance;
use App\Form\SubstanceType;
use App\Repository\SubstanceRepository;
use App\Service\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/substance')]
final class SubstanceController extends AbstractController
{
    #[Route(name: 'app_substance_index', methods: ['GET'])]
    public function index(SubstanceRepository $substanceRepository): Response
    {
        return $this->render('substance/index.html.twig', [
            'substances' => $substanceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_substance_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
        Uploader               $uploader
    ): Response
    {
        $substance = new Substance();
        $form = $this->createForm(SubstanceType::class, $substance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($substance);
            $entityManager->flush();

            return $this->redirectToRoute('app_substance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('substance/new.html.twig', [
            'substance' => $substance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_substance_show', methods: ['GET'])]
    public function show(Substance $substance): Response
    {
        return $this->render('substance/show.html.twig', [
            'substance' => $substance,
            'format' => $substance->getFormat()[0] ?? null,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_substance_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Substance $substance, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SubstanceType::class, $substance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_substance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('substance/edit.html.twig', [
            'substance' => $substance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_substance_delete', methods: ['POST'])]
    public function delete(Request $request, Substance $substance, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $substance->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($substance);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_substance_index', [], Response::HTTP_SEE_OTHER);
    }
}
