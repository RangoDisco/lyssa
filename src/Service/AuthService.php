<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class AuthService
{

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface      $em,
    )
    {
    }

    public function register(string $email, string $username, string $password): User
    {
        $user = new User()
            ->setEmail($email)
            ->setRoles([UserRole::User->value])
            ->setUsername($username);

        $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
