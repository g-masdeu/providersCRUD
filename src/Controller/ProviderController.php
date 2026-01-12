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
 * ProviderController
 * 
 * Gestiona el ciclo de vida de los proveedores (CRUD).
 * Soporta multiidioma mediante el parámetro {_locale} y operaciones vía AJAX para modales.
 * 
 * @author Tu Nombre / Candidato
 */
#[Route('/{_locale<%app.supported_locales%>}/provider')]
final class ProviderController extends AbstractController
{
    /**
     * Muestra el listado de proveedores activos.
     * 
     * @param ProviderRepository $repo Repositorio para consultas a la base de datos.
     * @return Response Vista con la tabla de proveedores.
     */
    #[Route('', name: 'app_provider_index', methods: ['GET'])]
    public function index(ProviderRepository $repo): Response
    {
        // Filtramos por active: true para implementar el borrado lógico (Soft Delete)
        $providers = $repo->findBy(['active' => true]);

        return $this->render('provider/index.html.twig', [
            'providers' => $providers,
        ]);
    }

    /**
     * Crea un nuevo registro de proveedor.
     * 
     * En GET: Devuelve el formulario vacío para el modal.
     * En POST: Procesa la validación y persiste en la base de datos.
     * 
     * @param Request $request Objeto de petición de Symfony.
     * @param EntityManagerInterface $em Gestor de entidades para persistencia.
     * @return Response Fragmento HTML para el modal o redirección al index.
     */
    #[Route('/new', name: 'app_provider_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $provider = new Provider();
        
        // Configuramos la acción del formulario explícitamente para evitar fallos en peticiones AJAX
        $form = $this->createForm(ProviderType::class, $provider, [
            'action' => $this->generateUrl('app_provider_new'),
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($provider);
            $em->flush();

            // Usamos claves de traducción para los mensajes flash (mantenibilidad i18n)
            $this->addFlash('success', 'flash.created');
            
            return $this->redirectToRoute('app_provider_index');
        }

        return $this->render('provider/_form_modal.html.twig', [
            'form' => $form->createView(),
            'provider' => $provider,
        ]);
    }

    /**
     * Edita un proveedor existente.
     * 
     * Utiliza el ParamConverter de Symfony para obtener automáticamente 
     * el objeto Provider a partir del {id} de la URL.
     * 
     * @param Request $request
     * @param Provider $provider Entidad inyectada automáticamente por ID.
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_provider_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Provider $provider, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProviderType::class, $provider, [
            'action' => $this->generateUrl('app_provider_edit', ['id' => $provider->getId()]),
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // No es necesario persist(), Doctrine detecta cambios en objetos gestionados (flushing)
            $em->flush();

            $this->addFlash('success', 'flash.updated');
            return $this->redirectToRoute('app_provider_index');
        }

        return $this->render('provider/_form_modal.html.twig', [
            'provider' => $provider,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Realiza un borrado lógico del proveedor.
     * 
     * Por seguridad y cumplimiento de estándares REST, solo acepta peticiones POST
     * y requiere la validación de un token CSRF.
     * 
     * @param Request $request
     * @param Provider $provider
     * @param EntityManagerInterface $em
     * @return Response Redirección al listado.
     */
    #[Route('/{id}/delete', name: 'app_provider_delete', methods: ['POST'])]
    public function delete(Request $request, Provider $provider, EntityManagerInterface $em): Response
    {
        // Validación rigurosa de seguridad contra ataques CSRF
        if ($this->isCsrfTokenValid('delete' . $provider->getId(), $request->request->get('_token'))) {
            
            // Implementación de Soft Delete: Mantenemos el registro para histórico contable
            $provider->setActive(false);
            $em->flush();
            
            $this->addFlash('warning', 'flash.deleted');
        }

        return $this->redirectToRoute('app_provider_index');
    }

    /**
     * Exporta el listado de proveedores activos a un archivo CSV.
     * Optimizado para ser abierto directamente en Excel.
     */
    #[Route('/export/csv', name: 'app_provider_export', methods: ['GET'])]
    public function exportCsv(ProviderRepository $repo): Response
    {
        $providers = $repo->findBy(['active' => true]);

        // Usamos un buffer de memoria para crear el CSV
        $handle = fopen('php://temp', 'r+');
        
        // Añadimos el BOM para que Excel detecte correctamente los acentos (UTF-8)
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        // Cabeceras del CSV (Traducibles si quieres, aquí fijas para el ejemplo)
        fputcsv($handle, ['Nombre', 'Email', 'Teléfono', 'Tipo', 'Fecha de Registro'], ';');

        foreach ($providers as $p) {
            fputcsv($handle, [
                $p->getName(),
                $p->getEmail(),
                $p->getPhone(),
                ucfirst($p->getType()),
                $p->getCreatedAt()->format('d/m/Y H:i')
            ], ';');
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return new Response($content, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="proveedores_contabilidad.csv"',
        ]);
    }
}