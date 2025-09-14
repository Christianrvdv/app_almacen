<?php

namespace App\Controller;

use App\Entity\HistorialPrecios;
use App\Form\HistorialPreciosType;
use App\Repository\HistorialPreciosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/historial/precios')]
final class HistorialPreciosController extends AbstractController
{
    #[Route(name: 'app_historial_precios_index', methods: ['GET'])]
    public function index(HistorialPreciosRepository $historialPreciosRepository): Response
    {
        return $this->render('historial_precios/index.html.twig', [
            'historial_precios' => $historialPreciosRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_historial_precios_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $historialPrecio = new HistorialPrecios();
        $form = $this->createForm(HistorialPreciosType::class, $historialPrecio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($historialPrecio);
            $entityManager->flush();

            return $this->redirectToRoute('app_historial_precios_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('historial_precios/new.html.twig', [
            'historial_precio' => $historialPrecio,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_historial_precios_show', methods: ['GET'])]
    public function show(HistorialPrecios $historialPrecio): Response
    {
        return $this->render('historial_precios/show.html.twig', [
            'historial_precio' => $historialPrecio,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_historial_precios_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, HistorialPrecios $historialPrecio, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HistorialPreciosType::class, $historialPrecio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_historial_precios_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('historial_precios/edit.html.twig', [
            'historial_precio' => $historialPrecio,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_historial_precios_delete', methods: ['POST'])]
    public function delete(Request $request, HistorialPrecios $historialPrecio, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$historialPrecio->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($historialPrecio);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_historial_precios_index', [], Response::HTTP_SEE_OTHER);
    }
}
