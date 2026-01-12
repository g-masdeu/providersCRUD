<?php

namespace App\Controller;

use App\Entity\Provider;
use App\Form\ProviderType;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * ProviderController
 * 
 * Clase principal para la gestión del ciclo de vida de los proveedores.
 * Implementa un diseño robusto con inyección de dependencias, manejo de 
 * excepciones y auditoría mediante logs.
 * 
 * @author Tu Nombre / Candidato
 */
#[Route('/{_locale<%app.supported_locales%>}/provider')]
final class ProviderController extends AbstractController
{
    /**
     * Constructor del controlador.
     * 
     * @param EntityManagerInterface $em Servicio para la gestión de la persistencia.
     * @param LoggerInterface $logger Servicio para el registro de eventos y errores del sistema.
     */
    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {}

    /**
     * Muestra el listado de proveedores activos.
     * 
     * @param ProviderRepository $repo Repositorio especializado en la entidad Provider.
     * @return Response Vista renderizada con el listado y componentes DataTables.
     */
    #[Route('', name: 'app_provider_index', methods: ['GET'])]
    public function index(ProviderRepository $repo): Response
    {
        return $this->render('provider/index.html.twig', [
            'providers' => $repo->findBy(['active' => true]),
        ]);
    }

    /**
     * Procesa la creación de un nuevo proveedor.
     * 
     * Incluye un bloque try-catch para gestionar fallos imprevistos en la persistencia
     * y registra cualquier anomalía en los logs del servidor para facilitar el soporte.
     * 
     * @param Request $request Petición HTTP entrante.
     * @return Response Redirección en éxito o fragmento HTML del formulario en caso de error/GET.
     */
    #[Route('/new', name: 'app_provider_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $provider = new Provider();
        $form = $this->createForm(ProviderType::class, $provider, [
            'action' => $this->generateUrl('app_provider_new'),
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->em->persist($provider);
                $this->em->flush();

                $this->addFlash('success', 'flash.created');
                return $this->redirectToRoute('app_provider_index');
            } catch (\Exception $e) {
                // Registro del error técnico para depuración
                $this->logger->error('Error al crear proveedor: ' . $e->getMessage(), [
                    'exception' => $e,
                    'data' => $request->request->all()
                ]);
                
                $this->addFlash('danger', 'flash.error_generic');
            }
        }

        return $this->render('provider/_form_modal.html.twig', [
            'form' => $form->createView(),
            'provider' => $provider,
        ]);
    }

    /**
     * Actualiza la información de un proveedor existente.
     * 
     * @param Request $request
     * @param Provider $provider Entidad cargada automáticamente vía ParamConverter.
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_provider_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Provider $provider): Response
    {
        $form = $this->createForm(ProviderType::class, $provider, [
            'action' => $this->generateUrl('app_provider_edit', ['id' => $provider->getId()]),
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->em->flush();
                $this->addFlash('success', 'flash.updated');
                return $this->redirectToRoute('app_provider_index');
            } catch (\Exception $e) {
                $this->logger->error('Error al editar proveedor ID ' . $provider->getId() . ': ' . $e->getMessage());
                $this->addFlash('danger', 'flash.error_generic');
            }
        }

        return $this->render('provider/_form_modal.html.twig', [
            'provider' => $provider,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Ejecuta el borrado lógico (desactivación) de un proveedor.
     * 
     * Se requiere validación de token CSRF para prevenir ataques de falsificación 
     * de peticiones en sitios cruzados.
     * 
     * @param Request $request
     * @param Provider $provider
     * @return Response
     */
    #[Route('/{id}/delete', name: 'app_provider_delete', methods: ['POST'])]
    public function delete(Request $request, Provider $provider): Response
    {
        if ($this->isCsrfTokenValid('delete' . $provider->getId(), $request->request->get('_token'))) {
            try {
                // 1. Marcamos como inactivo
                $provider->setActive(false);

                // 2. Liberamos los campos UNIQUE añadiendo un sufijo de borrado
                // Esto permite volver a crear un proveedor con los mismos datos originales
                $suffix = '-DEL-' . uniqid();
                
                $provider->setName($provider->getName() . ' (Borrado)');
                $provider->setEmail($provider->getEmail() . $suffix);
                
                // Ojo con la longitud del teléfono (el campo tiene 20 caracteres)
                // Cortamos el teléfono original si es necesario para que quepa el sello
                $cleanPhone = substr($provider->getPhone(), 0, 10) . $suffix;
                $provider->setPhone(substr($cleanPhone, 0, 20));

                $this->em->flush();
                $this->addFlash('warning', 'flash.deleted');
            } catch (\Exception $e) {
                $this->logger->error('Error al desactivar proveedor: ' . $e->getMessage());
                $this->addFlash('danger', 'flash.error_generic');
            }
        }

        return $this->redirectToRoute('app_provider_index');
    }

    /**
     * Exporta el listado de proveedores activos a formato CSV.
     * 
     * Diseñado para facilitar la integración de datos con herramientas contables externas (Excel).
     * Implementa manejo de codificación UTF-8 con BOM para asegurar la compatibilidad de caracteres.
     * 
     * @param ProviderRepository $repo
     * @return Response Archivo CSV descargable.
     */
    #[Route('/export/csv', name: 'app_provider_export', methods: ['GET'])]
    public function exportCsv(ProviderRepository $repo): Response
    {
        $providers = $repo->findBy(['active' => true]);
        
        $handle = fopen('php://temp', 'r+');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
        fputcsv($handle, ['Nombre', 'Email', 'Teléfono', 'Tipo', 'Fecha Registro'], ';');

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