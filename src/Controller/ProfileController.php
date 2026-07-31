<?php
// src/Controller/ProfileController.php
namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
// usually you'll want to make sure the user is authenticated first,
// see "Authorization" below
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/profile', name: 'app_profile')]
    public function profile(#[CurrentUser] User $user): Response
    {
        return $this->render('profile.html.twig', [
            'username' => $user->getUsername()
        ]);
    }
}
