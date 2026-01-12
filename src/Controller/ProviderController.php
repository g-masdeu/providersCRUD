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
 * Controlador para la gestión de Proveedores con soporte para Modales.
 */
final class ProviderController extends AbstractController
{
    /**
     * Muestra el listado principal.
     */
    #[Route('/provider', name: 'app_provider_index', methods: ['GET'])]
    public function index(ProviderRepository $repo): Response
    {
        // En el listado solemos querer solo los que están activos lógicamente
        $providers = $repo->findBy(['active' => true]);

        return $this->render('provider/index.html.twig', [
            'providers' => $providers,
        ]);
    }

    /**
     * Crear un nuevo proveedor.
     */
    #[Route('/provider/new', name: 'app_provider_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $provider = new Provider();
        $form = $this->createForm(ProviderType::class, $provider, [
            'action' => $this->generateUrl('app_provider_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($provider);
            $em->flush();

            $this->addFlash('success', 'Proveedor creado correctamente.');
            return $this->redirectToRoute('app_provider_index');
        }

        return $this->render('provider/_form_modal.html.twig', [
            'form' => $form->createView(),
            'provider' => $provider,
        ]);
    }

    /**
     * Edita un proveedor existente.
     * Al usar Modales, esta ruta suele cargar el formulario vía AJAX.
     * * @param Provider $provider Symfony busca automáticamente el ID en la BD
     */
    #[Route('/provider/{id}/edit', name: 'app_provider_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Provider $provider, EntityManagerInterface $em): Response
    {
        // El mismo formulario ProviderType sirve para editar
        $form = $this->createForm(ProviderType::class, $provider, [
            'action' => $this->generateUrl('app_provider_edit', ['id' => $provider->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // No hace falta persist() porque el objeto ya "existe" en Doctrine
            $em->flush();

            $this->addFlash('success', 'Proveedor actualizado correctamente.');
            return $this->redirectToRoute('app_provider_index');
        }

        return $this->render('provider/_form_modal.html.twig', [
            'provider' => $provider,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Borrado Lógico (Soft Delete).
     * No elimina el registro de la base de datos, solo cambia el estado 'active'.
     */
    #[Route('/provider/{id}/delete', name: 'app_provider_delete', methods: ['POST'])]
    public function delete(Request $request, Provider $provider, EntityManagerInterface $em): Response
    {
        // Verificamos el token CSRF por seguridad para evitar borrados accidentales/malintencionados
        if ($this->isCsrfTokenValid('delete' . $provider->getId(), $request->request->get('_token'))) {

            // --- BORRADO LÓGICO ---
            $provider->setActive(false);

            $em->flush();
            $this->addFlash('warning', 'El proveedor ha sido desactivado.');
        }

        return $this->redirectToRoute('app_provider_index');
    }
}
