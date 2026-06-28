<?php

namespace App\Security;

use App\Entity\Doctor;
use App\Entity\User;
use App\Enum\CaretakerAccessLevel;
use App\Repository\CaretakingAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\LogicException;

class DoctorVoter extends Voter
{
    public const DELETE = 'delete';
    public const EDIT = 'edit';
    public const SHOW = 'show';

    public function __construct(private readonly CaretakingAccessRepository $ca){}

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

        return match ($attribute) {
            self::SHOW => $this->canView($subject, $user, $vote),
            // EDIT also handle delete (for now)
            self::EDIT => $this->canEdit($subject, $user, $vote),
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
}
