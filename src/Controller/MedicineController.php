<?php

namespace App\Controller;

use App\DTO\Pagination\PaginationRequest;
use App\Entity\Medicine;
use App\Form\MedicineType;
use App\Service\MedicineService;
use App\Service\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/medicine')]
#[isGranted("IS_AUTHENTICATED")]
final class MedicineController extends AbstractController
{
    #[Route(name: 'app_medicine_index', methods: ['GET'])]
    public function index(
        MedicineService $medicineService,
        #[MapQueryString] PaginationRequest $pagination
    ): Response
    {
        return $this->render('medicine/index.html.twig', [
            'medicines' => $medicineService->getAccessible($this->getUser(), $pagination),
        ]);
    }

    #[Route('/new', name: 'app_medicine_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
        Uploader               $uploader,
    ): Response
    {
        $medicine = new Medicine();
        $form = $this->createForm(MedicineType::class, $medicine);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('media')->get('file')->getData();

            if ($file instanceof UploadedFile) {
                try {
                    $medicine->setPicture($uploader->handleFile($file));
                } catch (InvalidArgumentException|FileException) {
                    $form->get('media')->addError(new FormError("File is invalid."));
                }
            }

            if ($form->isValid()) {
                $entityManager->persist($medicine);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_medicine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('medicine/new.html.twig', [
            'medicine' => $medicine,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_medicine_show', methods: ['GET'])]
    #[IsGranted("MEDICINE_VIEW", subject: 'medicine')]
    public function show(Medicine $medicine): Response
    {
        return $this->render('medicine/show.html.twig', [
            'medicine' => $medicine,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_medicine_edit', methods: ['GET', 'POST'])]
    #[IsGranted("MEDICINE_EDIT", subject: 'medicine')]
    public function edit(Request $request, Medicine $medicine, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MedicineType::class, $medicine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_medicine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('medicine/edit.html.twig', [
            'medicine' => $medicine,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_medicine_delete', methods: ['POST'])]
    #[IsGranted("MEDICINE_DELETE", subject: 'medicine')]
    public function delete(Request $request, Medicine $medicine, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $medicine->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($medicine);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_medicine_index', [], Response::HTTP_SEE_OTHER);
    }
}
