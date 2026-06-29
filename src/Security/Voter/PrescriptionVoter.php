<?php

namespace App\Security\Voter;

use App\Entity\Prescription;
use App\Entity\User;
use App\Enum\CaretakerAccessLevel;
use App\Enum\UserRole;
use App\Repository\CaretakingAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\LogicException;

final class PrescriptionVoter extends Voter
{
    public const string EDIT = 'PRESCRIPTION_EDIT';
    public const string VIEW = 'PRESCRIPTION_VIEW';
    public const string DELETE = 'PRESCRIPTION_DELETE';

    public function __construct(
        private readonly CaretakingAccessRepository     $ca,
        private readonly AccessDecisionManagerInterface $adm
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Prescription;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            $vote?->addReason('The user must be logged in to access this resource.');

            return false;
        }

        if ($this->adm->decide($token, [UserRole::Admin])) {
            return true;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user, $vote),
            self::EDIT => $this->canEdit($subject, $user, $vote),
            self::DELETE => $this->canDelete($subject, $user, $vote),
            default => throw new LogicException("Invalid attribute.")
        };
    }

    private function canView(Prescription $prescription, User $user, Vote $vote): bool
    {
        if ($prescription->getPatient() === $user || $this->ca->isCaretakerOf($user, $prescription->getPatient())) {
            return true;
        }

        $vote->addReason("Only owner and caretakers can view a prescription.");

        return false;
    }

    private function canEdit(Prescription $prescription, User $user, Vote $vote): bool
    {
        if ($prescription->getPatient() === $user || $this->ca->isCaretakerOf($user, $prescription->getPatient(), CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only owner and caretakers with edit rights can edit a prescription.");

        return false;
    }

    private function canDelete(Prescription $prescription, User $user, Vote $vote): bool
    {
        if ($prescription->getPatient() === $user || $this->ca->isCaretakerOf($user, $prescription->getPatient(), CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only owner and caretakers with edit rights can delete a prescription.");

        return false;
    }
}
