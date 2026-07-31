<?php

namespace App\Security\Voter;

use App\Entity\Medicine;
use App\Entity\User;
use App\Enum\CaretakerAccessLevel;
use App\Enum\Source;
use App\Enum\UserRole;
use App\Repository\CaretakingAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\LogicException;

final class MedicineVoter extends Voter
{
    public const string EDIT = 'MEDICINE_EDIT';
    public const string VIEW = 'MEDICINE_VIEW';
    public const string DELETE = 'MEDICINE_DELETE';

    public function __construct(
        private readonly CaretakingAccessRepository     $ca,
        private readonly AccessDecisionManagerInterface $adm
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Medicine;
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

    private function canView(Medicine $medicine, User $user, Vote $vote): bool
    {
        if ($medicine->getSource() === Source::Official) {
            return true;
        }

        // TODO: see if in some case patient should see the medicine created by their caretakers
        if ($medicine->getOwner() === $user || $this->ca->isCaretakerOf($user, $medicine->getOwner())) {
            return true;
        }

        $vote->addReason("Only official medicine can be publicly viewed.");

        return false;
    }

    private function canEdit(Medicine $medicine, User $user, Vote $vote): bool
    {
        if ($medicine->getSource() === Source::Official) {
            $vote->addReason("Only community medicine can be edited.");
            return false;
        }

        if ($medicine->getOwner() === $user || $this->ca->isCaretakerOf($user, $medicine->getOwner(), CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only owners and caretakers can edit a medicine.");

        return false;
    }

    private function canDelete(Medicine $medicine, User $user, Vote $vote): bool
    {
        if ($medicine->getSource() === Source::Official) {
            $vote->addReason("Only community medicine can be deleted.");
            return false;
        }

        if ($medicine->getOwner() === $user || $this->ca->isCaretakerOf($user, $medicine->getOwner(), CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only owners and caretakers can be deleted.");
        return false;
    }
}
