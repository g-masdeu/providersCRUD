<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * AppHomeController
 * 
 * Controlador encargado de la orquestación de rutas raíz y redirecciones de alias.
 * Su objetivo es centralizar el tráfico entrante que no especifica un idioma
 * y redirigirlo al punto de entrada principal localizado.
 */
class AppHomeController extends AbstractController
{
    /**
     * Captura la petición a la raíz del dominio (/).
     * 
     * Redirige al usuario al listado de proveedores usando el idioma por defecto (Español).
     * Esto mejora la experiencia de usuario (UX) al evitar páginas en blanco o errores 404.
     * 
     * @return Response Redirección a la ruta localizada 'app_provider_index'.
     */
    #[Route('/', name: 'app_root_redirect')]
    public function root(): Response
    {
        return $this->redirectToRoute('app_provider_index', ['_locale' => 'es']);
    }

    /**
     * Captura y redirige URLs comunes o legadas.
     * 
     * Maneja alias comunes como '/provider' y '/providers' para asegurar que los usuarios
     * que escriben la URL manualmente o provienen de enlaces externos antiguos 
     * sean dirigidos correctamente a la sección de gestión.
     * 
     * @return Response Redirección a la ruta localizada 'app_provider_index'.
     */
    #[Route('/provider', name: 'app_provider_redirect')]
    #[Route('/providers', name: 'app_providers_redirect')]
    public function providerRedirect(): Response
    {
        // Centralizamos la redirección al locale principal configurado en la aplicación.
        return $this->redirectToRoute('app_provider_index', ['_locale' => 'es']);
    }
}