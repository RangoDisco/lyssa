<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Variant;
use App\Enum\CaretakerAccessLevel;
use App\Enum\MedicationSource;
use App\Enum\UserRole;
use App\Repository\VariantRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\LogicException;

final class VariantVoter extends Voter
{
    public const string VIEW = 'VARIANT_VIEW';
    public const string EDIT = 'VARIANT_EDIT';
    public const string DELETE = 'VARIANT_DELETE';

    public function __construct(
        private readonly VariantRepository              $variant,
        private readonly AccessDecisionManagerInterface $adm
    )
    {
    }


    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Variant;
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
            default => throw new LogicException("Invalid atttribute.")
        };
    }

    private function canView(Variant $variant, User $user, Vote $vote): bool
    {
        if ($variant->getMedication()->getSource() === MedicationSource::Official) {
            return true;
        }

        // TODO: see if in some case patient should see the medication created by their caretakers
        if ($this->variant->hasAccess($variant, $user)) {
            return true;
        }

        $vote->addReason("Only official medication can be publicly viewed.");

        return false;
    }

    private function canEdit(Variant $variant, User $user, Vote $vote): bool
    {
        if ($variant->getMedication()->getSource() === MedicationSource::Official) {
            $vote->addReason("Only community medication can be edited.");

            return false;
        }

        if ($this->variant->hasAccess($variant, $user, CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only patient and its caretakers (view edit rights) can edit variants related to them.");

        return false;
    }

    private function canDelete(Variant $variant, User $user, Vote $vote): bool
    {
        if ($variant->getMedication()->getSource() === MedicationSource::Official) {
            $vote->addReason("Only community medication can be deleted.");
            return false;
        }

        if ($this->variant->hasAccess($variant, $user, CaretakerAccessLevel::Edit)) {
            return true;
        }

        $vote->addReason("Only patient and its caretakers (with delete rights) can delete variants related to them.");

        return false;
    }
}
