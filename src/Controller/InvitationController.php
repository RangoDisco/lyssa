<?php

namespace App\Controller;

use App\DTO\Pagination\PaginationRequest;
use App\Entity\Invitation;
use App\Form\InvitationJoinType;
use App\Form\InvitationType;
use App\Service\InvitationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Throwable;

#[Route('/invitation')]
final class InvitationController extends AbstractController
{

    public function __construct(
        private readonly InvitationService $invitationService,
        private readonly LoggerInterface   $logger
    )
    {
    }

    #[Route(name: 'app_invitation_index', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED')]
    public function index(
        InvitationService                   $invitationService,
        #[MapQueryString] PaginationRequest $pagination,
    ): Response
    {
        return $this->render('invitation/index.html.twig', [
            'invitations' => $invitationService->getAccessible($this->getUser(), $pagination),
        ]);
    }

    #[Route('/new', name: 'app_invitation_new', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // TODO: handle already existing user
        $invitation = new Invitation();
        $form = $this->createForm(InvitationType::class, $invitation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invitation->setToken(Uuid::v4());

            try {
                $this->invitationService->send($invitation->getPatient(), $form->get("email")->getData(), $invitation->getToken());

                $entityManager->persist($invitation);
                $entityManager->flush();

                return $this->redirectToRoute('app_invitation_index', [], Response::HTTP_SEE_OTHER);
            } catch (TransportExceptionInterface $e) {
                $this->logger->error('Failed to send invitation link', [
                    'exception' => $e,
                    'patient' => $invitation->getPatient()->getId()
                ]);
                $form->addError(new FormError("Unable to send mail. Please try again later"));
            }
        }

        return $this->render('invitation/new.html.twig', [
            'invitation' => $invitation,
            'form' => $form,
        ]);
    }

    #[Route('/join/{token:invitation}', name: 'app_invitation_join', methods: ['GET', 'POST'])]
    public function join(
        Invitation $invitation,
        Security   $security,
        Request    $request,
    ): Response
    {
        $form = $this->createForm(InvitationJoinType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user = $this->invitationService->join($invitation, $form->get("username")->getData(), $form->get('plainPassword')->getData());

                return $security->login($user, 'form_login', 'main');
            } catch (Throwable $e) {
                $this->logger->error("Unable to join", [
                    'exception' => $e,
                    'invitation' => $invitation->getId()
                ]);
                $form->addError(new FormError("Unable to register. Please try again later"));
            }
        }

        return $this->render('invitation/new.html.twig', [
            'invitation' => $invitation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invitation_show', methods: ['GET'])]
    #[IsGranted('INVITATION_VIEW', subject: 'invitation')]
    public function show(Invitation $invitation): Response
    {
        return $this->render('invitation/show.html.twig', [
            'invitation' => $invitation,
        ]);
    }

    #[Route('/{id}', name: 'app_invitation_delete', methods: ['POST'])]
    #[IsGranted('INVITATION_DELETE', 'invitation')]
    public function delete(Request $request, Invitation $invitation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $invitation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($invitation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_invitation_index', [], Response::HTTP_SEE_OTHER);
    }
}
