<?php

namespace App\Voter;

use App\Entity\SpecialDayRequest;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Special day request voter
 */
class SpecialDayRequestVoter extends Voter
{

    public function __construct(
        private readonly Security $security
    ) {}

    public const READ = 'READ';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute == self::READ && $subject instanceof SpecialDayRequest;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted(LdapAdminVoter::ADMIN_ACCESS)) {
            return true;
        }
        if ($attribute === self::READ) {
            /** @var SpecialDayRequest $request */
            $request = $subject;
            return $this->security->getUser()->getUserIdentifier() === $request->getEmployee()->getUsername();
        }
        return false;
    }

}