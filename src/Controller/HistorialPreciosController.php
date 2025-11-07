<?php

namespace App\Controller;

use App\Entity\HistorialPrecios;
use App\Entity\Producto;
use App\Form\HistorialPreciosType;
use App\Service\HistorialPreciosOperationsInterface;
use App\Service\HistorialPreciosSearchInterface;
use App\Service\HistorialPreciosStatsInterface;
use App\Repository\HistorialPreciosRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/historial/precios')]
final class HistorialPreciosController extends AbstractController
{
    public function __construct(
        private HistorialPreciosSearchInterface $searchService,
        private HistorialPreciosStatsInterface $statsService,
        private HistorialPreciosOperationsInterface $operationsService,
        private HistorialPreciosRepository $repository
    ) {}

    #[Route(name: 'app_historial_precios_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchResult = $this->searchService->searchAndPaginate($request);
        $statistics = $this->statsService->getStatistics();

        return $this->render('historial_precios/index.html.twig', [
            'historial_precios' => $searchResult['pagination'],
            'searchTerm' => $searchResult['searchTerm'],
            'totalRegistros' => $statistics['totalRegistros'],
            'totalVenta' => $statistics['totalVenta'],
            'totalCompra' => $statistics['totalCompra'],
            'totalAjustePromo' => $statistics['totalAjustePromo'],
        ]);
    }

    #[Route('/new/{id}', name: 'app_historial_precios_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Producto $producto): Response
    {
        $historialPrecio = new HistorialPrecios();
        $historialPrecio->setProducto($producto);

        $form = $this->createForm(HistorialPreciosType::class, $historialPrecio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operationsService->createHistorialPrecios($historialPrecio, $producto);
                $this->addFlash('success', 'El historial de precios ha sido creado correctamente y el precio del producto ha sido actualizado.');

                return $this->redirectToRoute('app_producto_show', [
                    'id' => $producto->getId()
                ], Response::HTTP_SEE_OTHER);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al actualizar el precio: ' . $e->getMessage());
            }
        }

        return $this->render('historial_precios/new.html.twig', [
            'historial_precio' => $historialPrecio,
            'form' => $form,
            'producto' => $producto,
        ]);
    }

    #[Route('/{id}', name: 'app_historial_precios_show', methods: ['GET'])]
    public function show(HistorialPrecios $historialPrecio): Response
    {
        $ultimoVenta = $this->repository->findLastByProductAndType($historialPrecio->getProducto(), 'venta');
        $ultimoCompra = $this->repository->findLastByProductAndType($historialPrecio->getProducto(), 'compra');
        $esUltimoVenta = $ultimoVenta && $ultimoVenta->getId() === $historialPrecio->getId();
        $esUltimoCompra = $ultimoCompra && $ultimoCompra->getId() === $historialPrecio->getId();

        return $this->render('historial_precios/show.html.twig', [
            'historial_precio' => $historialPrecio,
            'ultimo_venta' => $esUltimoVenta,
            'ultimo_compra' => $esUltimoCompra,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_historial_precios_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, HistorialPrecios $historialPrecio): Response
    {
        $form = $this->createForm(HistorialPreciosType::class, $historialPrecio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operationsService->updateHistorialPrecios($historialPrecio);
                $this->addFlash('success', 'El historial de precios ha sido actualizado correctamente y el precio del producto ha sido actualizado.');

                return $this->redirectToRoute('app_historial_precios_show', [
                    'id' => $historialPrecio->getId()
                ], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al actualizar el historial de precios: ' . $e->getMessage());
            }
        }

        return $this->render('historial_precios/edit.html.twig', [
            'historial_precio' => $historialPrecio,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_historial_precios_delete_new', methods: ['POST'])]
    public function deleteNew(Request $request, HistorialPrecios $historialPrecio): Response
    {
        if ($this->isCsrfTokenValid('delete' . $historialPrecio->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $this->operationsService->deleteHistorialPrecios($historialPrecio);
                $this->addFlash('success', 'El historial de precios ha sido eliminado correctamente y el precio del producto ha sido revertido.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar el historial de precios: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token de seguridad inválido, no se pudo eliminar el historial de precios.');
        }

        return $this->redirectToRoute('app_producto_show', [
            'id' => $historialPrecio->getProducto()->getId()
        ], Response::HTTP_SEE_OTHER);
    }
}
