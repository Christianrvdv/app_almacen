<?php

namespace App\Controller;

use App\Entity\DetalleVenta;
use App\Form\DetalleVentaType;
use App\Service\DetalleVenta\Interface\DetalleVentaOperationsInterface;
use App\Service\DetalleVenta\Interface\DetalleVentaSearchInterface;
use App\Service\DetalleVenta\Interface\DetalleVentaStatsInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/detalle/venta')]
final class DetalleVentaController extends AbstractController
{
    public function __construct(
        private DetalleVentaSearchInterface $searchService,
        private DetalleVentaStatsInterface $statsService,
        private DetalleVentaOperationsInterface $operationsService
    ) {}

    #[Route(name: 'app_detalle_venta_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchResult = $this->searchService->searchAndPaginate($request);
        $statistics = $this->statsService->getStatistics();

        return $this->render('detalle_venta/index.html.twig', [
            'detalle_ventas' => $searchResult['pagination'],
            'searchTerm' => $searchResult['searchTerm'],
            'totalDetalles' => $statistics['totalDetalles'],
            'totalVentasConProducto' => $statistics['totalVentasConProducto'],
            'ingresosTotales' => $statistics['ingresosTotales'],
        ]);
    }

    #[Route('/new', name: 'app_detalle_venta_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $detalleVenta = new DetalleVenta();
        $form = $this->createForm(DetalleVentaType::class, $detalleVenta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operationsService->createDetalleVenta($detalleVenta);
                $this->addFlash('success', 'El detalle de venta ha sido creado correctamente.');
                return $this->redirectToRoute('app_detalle_venta_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al crear el detalle de venta: ' . $e->getMessage());
            }
        }

        return $this->render('detalle_venta/new.html.twig', [
            'detalle_venta' => $detalleVenta,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_detalle_venta_show', methods: ['GET'])]
    public function show(DetalleVenta $detalleVenta): Response
    {
        return $this->render('detalle_venta/show.html.twig', [
            'detalle_venta' => $detalleVenta,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_detalle_venta_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DetalleVenta $detalleVenta): Response
    {
        $form = $this->createForm(DetalleVentaType::class, $detalleVenta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operationsService->updateDetalleVenta($detalleVenta);
                $this->addFlash('success', 'El detalle de venta ha sido actualizado correctamente.');
                return $this->redirectToRoute('app_detalle_venta_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al actualizar el detalle de venta: ' . $e->getMessage());
            }
        }

        return $this->render('detalle_venta/edit.html.twig', [
            'detalle_venta' => $detalleVenta,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_detalle_venta_delete', methods: ['POST'])]
    public function delete(Request $request, DetalleVenta $detalleVenta): Response
    {
        if ($this->isCsrfTokenValid('delete'.$detalleVenta->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $this->operationsService->deleteDetalleVenta($detalleVenta);
                $this->addFlash('success', 'El detalle de venta ha sido eliminado correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar el detalle de venta: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token de seguridad inválido.');
        }

        return $this->redirectToRoute('app_detalle_venta_index', [], Response::HTTP_SEE_OTHER);
    }
}
