<?php

namespace App\Controller;

use App\Entity\HistorialPrecios;
use App\Entity\Producto;
use App\Form\HistorialPreciosType;
use App\Service\HistorialPrecios\Interface\HistorialPreciosServiceInterface;
use App\Service\HistorialPrecios\Interface\HistorialPreciosQueryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/historial/precios')]
final class HistorialPreciosController extends AbstractController
{
    public function __construct(
        private HistorialPreciosQueryInterface $queryService,
        private HistorialPreciosServiceInterface $operationsService
    ) {}

    #[Route(name: 'app_historial_precios_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchResult = $this->queryService->searchAndPaginate($request);
        $statistics = $this->queryService->getStatistics();

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

        return $this->handleHistorialForm($request, $historialPrecio, 'create');
    }

    #[Route('/{id}/edit', name: 'app_historial_precios_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, HistorialPrecios $historialPrecio): Response
    {
        return $this->handleHistorialForm($request, $historialPrecio, 'update');
    }

    private function handleHistorialForm(Request $request, HistorialPrecios $historialPrecio, string $operation): Response
    {
        $form = $this->createForm(HistorialPreciosType::class, $historialPrecio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($operation === 'create') {
                    $this->operationsService->create($historialPrecio);
                    $message = 'El historial de precios ha sido creado correctamente y el precio del producto ha sido actualizado.';
                    $redirectRoute = 'app_producto_show';
                    $redirectParams = ['id' => $historialPrecio->getProducto()->getId()];
                } else {
                    $this->operationsService->update($historialPrecio);
                    $message = 'El historial de precios ha sido actualizado correctamente y el precio del producto ha sido actualizado.';
                    $redirectRoute = 'app_historial_precios_show';
                    $redirectParams = ['id' => $historialPrecio->getId()];
                }

                $this->addFlash('success', $message);
                return $this->redirectToRoute($redirectRoute, $redirectParams, Response::HTTP_SEE_OTHER);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al procesar el historial de precios: ' . $e->getMessage());
            }
        }

        $template = $operation === 'create' ? 'new.html.twig' : 'edit.html.twig';
        return $this->render("historial_precios/{$template}", [
            'historial_precio' => $historialPrecio,
            'form' => $form,
            'producto' => $operation === 'create' ? $historialPrecio->getProducto() : null,
        ]);
    }

    #[Route('/{id}', name: 'app_historial_precios_delete', methods: ['POST'])]
    public function delete(Request $request, HistorialPrecios $historialPrecio): Response
    {
        if ($this->isCsrfTokenValid('delete'.$historialPrecio->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $this->operationsService->delete($historialPrecio);
                $this->addFlash('success', 'El historial de precios ha sido eliminado correctamente y el precio del producto ha sido revertido.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar el historial de precios: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Error de seguridad. No se pudo eliminar el historial de precios.');
        }

        return $this->redirectToRoute('app_producto_show', [
            'id' => $historialPrecio->getProducto()->getId()
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_historial_precios_show', methods: ['GET'])]
    public function show(HistorialPrecios $historialPrecio): Response
    {
        $ultimoVenta = $this->queryService->findLastByProductAndType($historialPrecio->getProducto(), 'venta');
        $ultimoCompra = $this->queryService->findLastByProductAndType($historialPrecio->getProducto(), 'compra');
        $esUltimoVenta = $ultimoVenta && $ultimoVenta->getId() === $historialPrecio->getId();
        $esUltimoCompra = $ultimoCompra && $ultimoCompra->getId() === $historialPrecio->getId();

        return $this->render('historial_precios/show.html.twig', [
            'historial_precio' => $historialPrecio,
            'ultimo_venta' => $esUltimoVenta,
            'ultimo_compra' => $esUltimoCompra,
        ]);
    }
}
