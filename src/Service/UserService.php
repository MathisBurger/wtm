<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\FormException;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

/**
 * General user service that handles user actions
 */
class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ){}

    /**
     * Creates the admin user
     *
     * @param FormInterface $form
     * @return void
     * @throws FormException
     */
    public function createAdminUser(FormInterface $form)
    {
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new FormException("Form is not submitted or valid");
        }
        $data = $form->getData();
        if ($data instanceof User) {
            $data->setPassword($this->passwordHasher->hashPassword($data, $data->getPassword()));
            $data->setRoles(array('ROLE_ADMIN'));
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }
    }
}