<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Variant;
use App\Form\VariantType;
use App\Repository\VariantRepository;
use App\Service\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/variant')]
#[isGranted("IS_AUTHENTICATED")]
final class VariantController extends AbstractController
{
    #[Route(name: 'app_variant_index', methods: ['GET'])]
    public function index(VariantRepository $variantRepository): Response
    {
        return $this->render('variant/index.html.twig', [
            'variants' => $variantRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_variant_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
        Uploader               $uploader,
    ): Response
    {
        $variant = new Variant();
        $form = $this->createForm(VariantType::class, $variant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($variant);
            $entityManager->flush();

            return $this->redirectToRoute('app_variant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('variant/new.html.twig', [
            'variant' => $variant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_variant_show', methods: ['GET'])]
    #[IsGranted("VARIANT_VIEW", subject: 'variant')]
    public function show(Variant $variant): Response
    {
        return $this->render('variant/show.html.twig', [
            'variant' => $variant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_variant_edit', methods: ['GET', 'POST'])]
    #[IsGranted("VARiANT_EDIT", subject: 'variant')]
    public function edit(Request $request, Variant $variant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VariantType::class, $variant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_variant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('variant/edit.html.twig', [
            'variant' => $variant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_variant_delete', methods: ['POST'])]
    #[IsGranted("VARiANT_DELETE", subject: 'variant')]
    public function delete(Request $request, Variant $variant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $variant->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($variant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_variant_index', [], Response::HTTP_SEE_OTHER);
    }
}
