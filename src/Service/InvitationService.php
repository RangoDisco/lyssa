<?php

namespace App\Service;

use App\DTO\Pagination\PaginatedResult;
use App\DTO\Pagination\PaginationRequest;
use App\Entity\CaretakingAccess;
use App\Entity\Invitation;
use App\Entity\User;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class InvitationService
{

    public function __construct(
        private MailerInterface        $mailer,
        private InvitationRepository   $invitationRepository,
        private PaginationService      $paginationService,
        private AuthService            $auth,
        private EntityManagerInterface $em
    )
    {
    }

    public function getAccessible(User|UserInterface $user, PaginationRequest $pagination): PaginatedResult
    {
        $query = $this->invitationRepository->createAcessibleQueryBuilder($user);
        return $this->paginationService->paginate($query, $pagination);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send(User $user, string $emailAddress, string $token): void
    {
        $email = new Email()
            ->from('Lyssa <notification@lyssa.rangodisco.eu>')
            ->to($emailAddress)
            ->subject(sprintf("%s invited you.", $user->getUsername()))
            ->text("Join them")
            ->html("<a href='https://localhost/invitation/join?token=$token'>Join</a>");

        $this->mailer->send($email);
    }

    public function join(Invitation $invitation, string $username, string $password): User
    {
        // Register user
        $user = $this->auth->register($invitation->getEmail(), $username, $password);

        // Add to caretakers
        $ca = new CaretakingAccess()
            ->setCaretaker($user)
            ->setPatient($invitation->getPatient())
            ->setLevel($invitation->getAccessLevel());

        $this->em->persist($ca);
        $this->em->remove($invitation);
        $this->em->flush();

        return $user;
    }
}
