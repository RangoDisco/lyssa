<?php

namespace App\Security\Voter;

use App\Entity\Invitation;
use App\Entity\User;
use App\Enum\CaretakerAccessLevel;
use App\Enum\UserRole;
use App\Repository\InvitationRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\LogicException;
use Symfony\Component\Security\Core\User\UserInterface;

final class InvitationVoter extends Voter
{
    public const string EDIT = 'INVITATION_EDIT';
    public const string VIEW = 'INVITATION_VIEW';
    public const string DELETE = 'INVITATION_DELETE';

    public function __construct(
        private readonly InvitationRepository           $invitation,
        private readonly AccessDecisionManagerInterface $adm
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Invitation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            $vote?->addReason('The user must be logged in to access this resource.');

            return false;
        }

        if ($this->adm->decide($token, [UserRole::Admin->value])) {
            return true;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user, $vote),
            self::EDIT => $this->canEdit($subject, $user, $vote),
            self::DELETE => $this->canDelete($subject, $user, $vote),
            default => throw new LogicException("Invalid attribute.")
        };
    }

    private function canView(Invitation $invitation, User $user, Vote $vote): bool
    {
        if ($this->invitation->hasAccess($user, $invitation)) {
            return true;
        }

        $vote->addReason("Only patient and its caretakers can view invitations related to them.");

        return false;
    }

    private function canEdit(Invitation $invitation, User $user, Vote $vote): bool
    {
        if ($this->invitation->hasAccess($user, $invitation, CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only patient and its caretakers (view edit rights) can edit an invitation related to them.");

        return false;
    }

    private function canDelete(Invitation $invitation, User $user, Vote $vote): bool
    {
        if ($this->invitation->hasAccess($user, $invitation, CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only patient and its caretakers (view delete rights) can delete an invitation related to them.");

        return false;
    }
}
