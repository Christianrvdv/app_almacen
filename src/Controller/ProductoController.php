<?php

namespace App\Controller;

use App\Entity\Producto;
use App\Form\ProductoType;
use App\Service\ProductoOperationsInterface;
use App\Service\ProductoSearchInterface;
use App\Service\ProductoStatsInterface;
use App\Service\InventoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/producto')]
final class ProductoController extends AbstractController
{
    public function __construct(
        private ProductoSearchInterface $searchService,
        private ProductoStatsInterface $statsService,
        private ProductoOperationsInterface $operationsService,
        private InventoryService $inventoryService
    ) {}

    #[Route('', name: 'app_producto_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchResult = $this->searchService->searchAndPaginate($request);
        $statistics = $this->statsService->getStatistics();

        return $this->render('producto/index.html.twig', [
            'productos' => $searchResult['pagination'],
            'totalProductos' => $statistics['totalProductos'],
            'totalActivos' => $statistics['totalActivos'],
            'totalInactivos' => $statistics['totalInactivos'],
            'totalConCategoria' => $statistics['totalConCategoria'],
            'searchTerm' => $searchResult['searchTerm'],
        ]);
    }

    #[Route('/new', name: 'app_producto_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $producto = new Producto();
        $form = $this->createForm(ProductoType::class, $producto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operationsService->createProducto($producto);
                $this->addFlash('success', 'El producto ha sido creado correctamente.');
                return $this->redirectToRoute('app_producto_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al crear el producto: ' . $e->getMessage());
            }
        }

        return $this->render('producto/new.html.twig', [
            'producto' => $producto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_producto_show', methods: ['GET'])]
    public function show(Producto $producto): Response
    {
        $stats = $this->inventoryService->calculateProductStats($producto);

        return $this->render('producto/show.html.twig', [
            'producto' => $producto,
            'stok' => $stats['stock'],
            'ingresos' => $stats['ingresos'],
            'ventas' => $stats['ventas'],
            'margen' => $stats['margen'],
            'modificaciones' => $stats['modificaciones'],
        ]);
    }

    #[Route('/{id}/edit', name: 'app_producto_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Producto $producto): Response
    {
        $form = $this->createForm(ProductoType::class, $producto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operationsService->updateProducto($producto);
                $this->addFlash('success', 'El producto ha sido actualizado correctamente.');
                return $this->redirectToRoute('app_producto_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al actualizar el producto: ' . $e->getMessage());
            }
        }

        return $this->render('producto/edit.html.twig', [
            'producto' => $producto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_producto_delete', methods: ['POST'])]
    public function delete(Request $request, Producto $producto): Response
    {
        if ($this->isCsrfTokenValid('delete'.$producto->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $this->operationsService->deleteProducto($producto);
                $this->addFlash('success', 'El producto ha sido eliminado correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar el producto: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Error de seguridad. No se pudo eliminar el producto.');
        }

        return $this->redirectToRoute('app_producto_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/stats', name: 'app_producto_stats', methods: ['GET'])]
    public function getStats(Producto $producto): Response
    {
        $stats = $this->inventoryService->calculateProductStats($producto);

        return $this->json([
            'ventas_totales' => $stats['ventas'],
            'stock_actual' => $stats['stock'],
            'ingresos_generados' => $stats['ingresos'],
            'veces_modificado' => $stats['modificaciones'],
        ]);
    }
}
