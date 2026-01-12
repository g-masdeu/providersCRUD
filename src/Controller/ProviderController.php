<?php

namespace App\Controller;

use App\Entity\Provider;
use App\Form\ProviderType;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador principal para la gestión de Proveedores.
 */
final class ProviderController extends AbstractController
{
    /**
     * Muestra la lista de todos los proveedores registrados.
     * * @param ProviderRepository $repo El repositorio se inyecta automáticamente mediante Autowiring.
     * @return Response Una respuesta que renderiza la plantilla de índice.
     */
    #[Route('/provider', name: 'app_provider_index', methods: ['GET'])]
    public function index(ProviderRepository $repo): Response
    {
        // Recuperamos todos los registros de la tabla 'provider'
        $providers = $repo->findAll();

        return $this->render('provider/index.html.twig', [
            'providers' => $providers,
        ]);
    }

    /**
     * Gestiona la creación de un nuevo proveedor.
     * Este método maneja tanto la visualización del formulario (GET) como el procesado de los datos (POST).
     * * @param Request $request Objeto que contiene los datos de la petición HTTP.
     * @param EntityManagerInterface $em El gestor de entidades de Doctrine para guardar en la BD.
     * @return Response
     */
    #[Route('/provider/new', name: 'app_provider_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // 1. Instanciamos el objeto de la entidad
        $provider = new Provider();

        // 2. Creamos el formulario basado en la clase ProviderType que definimos previamente
        $form = $this->createForm(ProviderType::class, $provider);

        // 3. Inspeccionamos si la petición es un POST y vinculamos los datos del formulario al objeto $provider
        $form->handleRequest($request);

        // 4. Validamos el formulario
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Persistimos el objeto
            $em->persist($provider);
            
            // Sincronizamos con la base de datos
            $em->flush();

            // Mensaje de éxito
            $this->addFlash('success', 'Proveedor creado correctamente.');

            // Redirigimos al listado principal
            return $this->redirectToRoute('app_provider_index');
        }

        // 5. Si el formulario no se ha enviado o no es válido, renderizamos la vista del formulario
        return $this->render('provider/new.html.twig', [
            'provider' => $provider,
            'form' => $form,
        ]);
    }
}