<?php

namespace App\Controller;

use App\Entity\DetalleCompra;
use App\Form\DetalleCompraType;
use App\Service\DetalleCompra\Interface\DetalleCompraQueryInterface;
use App\Service\DetalleCompra\Interface\DetalleCompraServiceInterface;
use App\Service\DetalleCompra\Interface\DetalleCompraRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/detalle/compra')]
final class DetalleCompraController extends AbstractController
{
    public function __construct(
        private DetalleCompraQueryInterface $queryService,
        private DetalleCompraServiceInterface $operationsService
    ) {}

    #[Route(name: 'app_detalle_compra_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchResult = $this->queryService->searchAndPaginate($request);
        $statistics = $this->queryService->getStatistics();

        return $this->render('detalle_compra/index.html.twig', [
            'detalle_compras' => $searchResult['pagination'],
            'totalDetalles' => $statistics['totalDetalles'],
            'totalConProducto' => $statistics['totalConProducto'],
            'sumaSubtotal' => $statistics['sumaSubtotal'],
            'searchTerm' => $searchResult['searchTerm'],
        ]);
    }

    #[Route('/new', name: 'app_detalle_compra_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $detalleCompra = new DetalleCompra();
        return $this->handleDetalleCompraForm($request, $detalleCompra, 'create');
    }

    #[Route('/{id}/edit', name: 'app_detalle_compra_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DetalleCompra $detalleCompra): Response
    {
        return $this->handleDetalleCompraForm($request, $detalleCompra, 'update');
    }

    private function handleDetalleCompraForm(Request $request, DetalleCompra $detalleCompra, string $operation): Response
    {
        $form = $this->createForm(DetalleCompraType::class, $detalleCompra);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($operation === 'create') {
                    $this->operationsService->create($detalleCompra);
                    $message = 'El detalle de compra ha sido creado correctamente.';
                    $redirectRoute = 'app_detalle_compra_index';
                } else {
                    $this->operationsService->update($detalleCompra);
                    $message = 'El detalle de compra ha sido actualizado correctamente.';
                    $redirectRoute = 'app_detalle_compra_show';
                }

                $this->addFlash('success', $message);
                return $this->redirectToRoute(
                    $redirectRoute,
                    $operation === 'update' ? ['id' => $detalleCompra->getId()] : [],
                    Response::HTTP_SEE_OTHER
                );
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al procesar el detalle de compra: ' . $e->getMessage());
            }
        }

        $template = $operation === 'create' ? 'new.html.twig' : 'edit.html.twig';
        return $this->render("detalle_compra/{$template}", [
            'detalle_compra' => $detalleCompra,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_detalle_compra_show', methods: ['GET'])]
    public function show(DetalleCompra $detalleCompra): Response
    {
        return $this->render('detalle_compra/show.html.twig', [
            'detalle_compra' => $detalleCompra,
        ]);
    }

    #[Route('/by/{id}', name: 'app_detalle_compra_show_by_id', methods: ['GET'])]
    public function getDetalleByCompra($id, DetalleCompraRepositoryInterface $detalleCompraRepository): Response
    {
        $detalleCompras = $detalleCompraRepository->findByCompraId($id);
        return $this->render('detalle_compra/index.html.twig', [
            'detalle_compras' => $detalleCompras,
        ]);
    }

    #[Route('/{id}', name: 'app_detalle_compra_delete', methods: ['POST'])]
    public function delete(Request $request, DetalleCompra $detalleCompra): Response
    {
        if ($this->isCsrfTokenValid('delete' . $detalleCompra->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $this->operationsService->delete($detalleCompra);
                $this->addFlash('success', 'El detalle de compra ha sido eliminado correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar el detalle de compra: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_detalle_compra_index', [], Response::HTTP_SEE_OTHER);
    }
}
