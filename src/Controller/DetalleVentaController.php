<?php

namespace App\Controller;

use App\Entity\DetalleVenta;
use App\Form\DetalleVentaType;
use App\Repository\DetalleVentaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/detalle/venta')]
final class DetalleVentaController extends AbstractController
{
    #[Route(name: 'app_detalle_venta_index', methods: ['GET'])]
    public function index(DetalleVentaRepository $detalleVentaRepository): Response
    {
        return $this->render('detalle_venta/index.html.twig', [
            'detalle_ventas' => $detalleVentaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_detalle_venta_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $detalleVentum = new DetalleVenta();
        $form = $this->createForm(DetalleVentaType::class, $detalleVentum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($detalleVentum);
            $entityManager->flush();

            return $this->redirectToRoute('app_detalle_venta_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('detalle_venta/new.html.twig', [
            'detalle_ventum' => $detalleVentum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_detalle_venta_show', methods: ['GET'])]
    public function show(DetalleVenta $detalleVentum): Response
    {
        return $this->render('detalle_venta/show.html.twig', [
            'detalle_ventum' => $detalleVentum,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_detalle_venta_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DetalleVenta $detalleVentum, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DetalleVentaType::class, $detalleVentum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_detalle_venta_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('detalle_venta/edit.html.twig', [
            'detalle_ventum' => $detalleVentum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_detalle_venta_delete', methods: ['POST'])]
    public function delete(Request $request, DetalleVenta $detalleVentum, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$detalleVentum->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($detalleVentum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_detalle_venta_index', [], Response::HTTP_SEE_OTHER);
    }
}
