<?php

namespace App\Security\Voter;

use App\Entity\Medicine;
use App\Entity\Substance;
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

final class SubstanceVoter extends Voter
{
    public const string EDIT = 'SUBSTANCE_EDIT';
    public const string VIEW = 'SUBSTANCE_VIEW';
    public const string DELETE = 'SUBSTANCE_DELETE';

    public function __construct(
        private readonly CaretakingAccessRepository     $ca,
        private readonly AccessDecisionManagerInterface $adm
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Substance;
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

    private function canView(Substance $substance, User $user, Vote $vote): bool
    {
        if ($substance->getSource() === Source::Official) {
            return true;
        }

        // TODO: see if in some case patient should see the substance created by their caretakers
        if ($substance->getOwner() === $user || $this->ca->isCaretakerOf($user, $substance->getOwner())) {
            return true;
        }

        $vote->addReason("Only official medicine can be publicly viewed.");

        return false;
    }

    private function canEdit(Substance $substance, User $user, Vote $vote): bool
    {
        if ($substance->getSource() === Source::Official) {
            $vote->addReason("Only community substance can be edited.");
            return false;
        }

        if ($substance->getOwner() === $user || $this->ca->isCaretakerOf($user, $substance->getOwner(), CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only owners and caretakers can edit a substance.");

        return false;
    }

    private function canDelete(Substance $substance, User $user, Vote $vote): bool
    {
        if ($substance->getSource() === Source::Official) {
            $vote->addReason("Only community substance can be deleted.");
            return false;
        }

        if ($substance->getOwner() === $user || $this->ca->isCaretakerOf($user, $substance->getOwner(), CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only owners and caretakers can delete a substance.");
        return false;
    }
}
