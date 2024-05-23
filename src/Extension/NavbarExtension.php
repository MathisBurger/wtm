<?php

namespace App\Extension;

use App\Repository\EmployeeRepository;
use App\Voter\LdapAdminVoter;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\Ldap\Security\LdapUser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension for providing data
 */
class NavbarExtension extends AbstractExtension
{

    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly Security $security,
    ) {}

    public function getName(): string
    {
        return 'WtmNavbarExtension';
    }

    public function getFunctions(): array
    {
        return [
            'getCurrentEmployeeId' => new TwigFunction('getCurrentEmployeeId', [$this, 'getCurrentEmployeeId']),
        ];
    }

    /**
     * Gets the ID of the current employee
     *
     * @return int The ID
     */
    public function getCurrentEmployeeId(): int
    {
        if ($this->security->isGranted(LdapAdminVoter::PERSONAL_STATS_ACCESS)) {
            /** @var LdapUser $user */
            $user = $this->security->getUser();
            return $this->employeeRepository->findOneBy(['username' => $user->getUserIdentifier()])->getId();
        }
        return -1;
    }

}