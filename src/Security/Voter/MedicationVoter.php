<?php

namespace App\Security\Voter;

use App\Entity\Medication;
use App\Entity\User;
use App\Enum\CaretakerAccessLevel;
use App\Enum\MedicationSource;
use App\Enum\UserRole;
use App\Repository\CaretakingAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\LogicException;

final class MedicationVoter extends Voter
{
    public const string EDIT = 'MEDICATION_EDIT';
    public const string VIEW = 'MEDICATION_VIEW';
    public const string DELETE = 'MEDICATION_DELETE';

    public function __construct(
        private readonly CaretakingAccessRepository     $ca,
        private readonly AccessDecisionManagerInterface $adm
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Medication;
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
            default => throw new LogicException("Invalid attribute")
        };
    }

    private function canView(Medication $medication, User $user, Vote $vote): bool
    {
        if ($medication->getSource() === MedicationSource::Official) {
            return true;
        }

        // TODO: see if in some case patient should see the medication created by their caretakers
        if ($medication->getOwner() === $user || $this->ca->isCaretakerOf($user, $medication->getOwner())) {
            return true;
        }

        $vote->addReason("Only official medication can be publicly viewed.");

        return false;
    }

    private function canEdit(Medication $medication, User $user, Vote $vote): bool
    {
        if ($medication->getSource() === MedicationSource::Official) {
            $vote->addReason("Only community medication can be edited.");
            return false;
        }

        if ($medication->getOwner() === $user || $this->ca->isCaretakerOf($user, $medication->getOwner(), CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only owners and caretakers can edit a medication.");

        return false;
    }

    private function canDelete(Medication $medication, User $user, Vote $vote): bool
    {
        if ($medication->getSource() === MedicationSource::Official) {
            $vote->addReason("Only community medication can be deleted.");
            return false;
        }

        if ($medication->getOwner() === $user || $this->ca->isCaretakerOf($user, $medication->getOwner(), CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only owners and caretakers can be deleted.");
        return false;
    }
}
