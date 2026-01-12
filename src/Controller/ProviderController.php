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
 * CONTROLADOR PRINCIPAL CON IDIOMA
 */
#[Route('/{_locale<%app.supported_locales%>}/provider')]
final class ProviderController extends AbstractController
{
    #[Route('', name: 'app_provider_index', methods: ['GET'])]
    public function index(ProviderRepository $repo): Response
    {
        $providers = $repo->findBy(['active' => true]);
        return $this->render('provider/index.html.twig', ['providers' => $providers]);
    }

    #[Route('/new', name: 'app_provider_new', methods: ['GET', 'POST'])]
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
            $this->addFlash('success', 'flash.created');
            return $this->redirectToRoute('app_provider_index');
        }

        return $this->render('provider/_form_modal.html.twig', [
            'form' => $form->createView(),
            'provider' => $provider,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_provider_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Provider $provider, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProviderType::class, $provider, [
            'action' => $this->generateUrl('app_provider_edit', ['id' => $provider->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'flash.updated');
            return $this->redirectToRoute('app_provider_index');
        }

        return $this->render('provider/_form_modal.html.twig', [
            'provider' => $provider,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_provider_delete', methods: ['POST'])]
    public function delete(Request $request, Provider $provider, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $provider->getId(), $request->request->get('_token'))) {
            $provider->setActive(false);
            $em->flush();
            $this->addFlash('warning', 'flash.deleted');
        }
        return $this->redirectToRoute('app_provider_index');
    }
}