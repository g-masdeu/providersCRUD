<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AppHomeController extends AbstractController
{
    // Captura la raíz "/"
    #[Route('/', name: 'app_root_redirect')]
    public function root(): Response
    {
        return $this->redirectToRoute('app_provider_index', ['_locale' => 'es']);
    }

    // Captura "/provider" y "/providers" (por si acaso)
    #[Route('/provider', name: 'app_provider_redirect')]
    #[Route('/providers', name: 'app_providers_redirect')]
    public function providerRedirect(): Response
    {
        return $this->redirectToRoute('app_provider_index', ['_locale' => 'es']);
    }
}