<?php

namespace App\Security\Voter;

use App\Entity\Doctor;
use App\Entity\User;
use App\Enum\CaretakerAccessLevel;
use App\Enum\UserRole;
use App\Repository\CaretakingAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\LogicException;

class DoctorVoter extends Voter
{
    public const string DELETE = 'DOCTOR_DELETE';
    public const string EDIT = 'DOCTOR_EDIT';
    public const string SHOW = 'DOCTOR_SHOW';

    public function __construct(
        private readonly CaretakingAccessRepository     $ca,
        private readonly AccessDecisionManagerInterface $adm,
    )
    {
    }

    public function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Doctor && in_array($attribute, [self::SHOW, self::EDIT, self::DELETE], true);
    }

    /**
     * @param Doctor $subject
     */
    public function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            $vote->addReason(sprintf("User should be authenticated to %s a doctor.", $attribute));

            return false;
        }

        if ($this->adm->decide($token, [UserRole::Admin->value])) {
            return true;
        }

        return match ($attribute) {
            self::SHOW => $this->canView($subject, $user, $vote),
            // EDIT also handle delete (for now)
            self::EDIT => $this->canEdit($subject, $user, $vote),
            self::DELETE => $this->canDelete($subject, $user, $vote),
            default => throw new LogicException("Invalid attribute provided")
        };
    }

    private function canView(Doctor $doctor, User $user, Vote $vote): bool
    {
        if ($doctor->getOwner() === $user || $this->ca->isCaretakerOf($user, $doctor->getOwner())) {
            return true;
        }

        $vote->addReason("Only owner and caretakers can view a doctor");

        return false;
    }

    private function canEdit(Doctor $doctor, User $user, Vote $vote): bool
    {
        if ($doctor->getOwner() === $user) {
            return true;
        }

        // Current user should have an existing CaretakingAccess with Edit rights and the given doctor's owner as the patient.
        if ($this->ca->isCaretakerOf($user, $doctor->getOwner(), CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only owners, or caretakers with edit rights can edit a doctor");

        return false;
    }

    private function canDelete(Doctor $doctor, User $user, Vote $vote): bool
    {
        if ($doctor->getOwner() === $user) {
            return true;
        }

        // Current user should have an existing CaretakingAccess with Edit rights and the given doctor's owner as the patient.
        if ($this->ca->isCaretakerOf($user, $doctor->getOwner(), CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only owners, or caretakers with edit rights can delete a doctor");

        return false;
    }
}
