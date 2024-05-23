<?php

namespace App\Voter;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Ldap\Security\LdapUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter that votes on LDAP users to determine permissions
 */
class LdapAdminVoter extends Voter
{
    /**
     * If a user has admin permissions
     */
    const ADMIN_ACCESS = 'LDAP_ADMIN_ACCESS';
    /**
     * If a user has default permissions
     */
    const DEFAULT_ACCESS = 'LDAP_DEFAULT_ACCESS';
    /**
     * If a user has IT permissions
     */
    const IT_ACCESS = 'LDAP_IT_ACCESS';

    public function __construct(
       private readonly KernelInterface $kernel
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::ADMIN_ACCESS, self::DEFAULT_ACCESS, self::IT_ACCESS])) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$token->getUser() instanceof LdapUser) {
            return false;
        }
        /** @var LdapUser $user */
        $user = $token->getUser();
        $memberOf = $user->getEntry()->getAttribute('memberOf');
        $ldapAdminGroup = $this->kernel->getContainer()->getParameter('ldap_admin_group');
        $ldapItGroup = $this->kernel->getContainer()->getParameter('ldap_it_group');
        if ($attribute === self::ADMIN_ACCESS) {
            return in_array($ldapAdminGroup, $memberOf);
        }
        if ($attribute === self::IT_ACCESS) {
            return in_array($ldapItGroup, $memberOf);
        }
        return true;
    }
}