<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ApiController extends AbstractController
{
    #[Route('/api-keys', name: 'app_api_keys')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig');
    }
}
