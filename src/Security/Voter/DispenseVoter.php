<?php

namespace App\Security\Voter;

use App\Entity\Dispense;
use App\Entity\User;
use App\Enum\CaretakerAccessLevel;
use App\Enum\UserRole;
use App\Repository\DispenseRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\LogicException;

final class DispenseVoter extends Voter
{
    public const EDIT = 'DISPENSE_EDIT';
    public const VIEW = 'DISPENSE_VIEW';
    public const DELETE = 'DISPENSE_DELETE';

    public function __construct(
        private readonly DispenseRepository             $dispense,
        private readonly AccessDecisionManagerInterface $adm
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof Dispense;
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

    private function canView(Dispense $dispense, User $user, Vote $vote): bool
    {
        if ($this->dispense->hasAccess($user, $dispense)) {
            return true;
        }

        $vote->addReason("Only patient and its caretakers can view dispenses related to them.");

        return false;
    }

    private function canEdit(Dispense $dispense, User $user, Vote $vote): bool
    {
        if ($this->dispense->hasAccess($user, $dispense, CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only patient and its caretakers (view edit rights) can edit dispenses related to them.");

        return false;
    }

    private function canDelete(Dispense $dispense, User $user, Vote $vote): bool
    {
        if ($this->dispense->hasAccess($user, $dispense, CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only patient and its caretakers (with delete rights) can delete dispenses related to them.");

        return false;
    }
}
