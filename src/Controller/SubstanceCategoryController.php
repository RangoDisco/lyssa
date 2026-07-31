<?php

namespace App\Controller;

use App\Entity\SubstanceCategory;
use App\Form\SubstanceCategoryType;
use App\Repository\SubstanceCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/substance_category')]
#[IsGranted('IS_AUTHENTICATED')]
final class SubstanceCategoryController extends AbstractController
{
    #[Route(name: 'app_substance_category_index', methods: ['GET'])]
    public function index(SubstanceCategoryRepository $SubstanceCategoryRepository): Response
    {
        return $this->render('substance_category/index.html.twig', [
            'substance_categories' => $SubstanceCategoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_substance_category_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $SubstanceCategory = new SubstanceCategory();
        $form = $this->createForm(SubstanceCategoryType::class, $SubstanceCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($SubstanceCategory);
            $entityManager->flush();

            return $this->redirectToRoute('app_substance_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('substance_category/new.html.twig', [
            'substance_category' => $SubstanceCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_substance_category_show', methods: ['GET'])]
    public function show(SubstanceCategory $SubstanceCategory): Response
    {
        return $this->render('substance_category/show.html.twig', [
            'substance_category' => $SubstanceCategory,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_substance_category_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, SubstanceCategory $SubstanceCategory, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SubstanceCategoryType::class, $SubstanceCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_substance_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('substance_category/edit.html.twig', [
            'substance_category' => $SubstanceCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_substance_category_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, SubstanceCategory $SubstanceCategory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$SubstanceCategory->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($SubstanceCategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_substance_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
