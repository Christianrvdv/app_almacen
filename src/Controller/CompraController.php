<?php

namespace App\Controller;

use App\Entity\Compra;
use App\Entity\DetalleCompra;
use App\Entity\Producto;
use App\Form\CompraType;
use App\Form\DetalleCompraType;
use App\Service\Compra\Interface\CompraQueryInterface;
use App\Service\Compra\Interface\CompraServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/compra')]
final class CompraController extends AbstractController
{
    public function __construct(
        private CompraQueryInterface $queryService,
        private CompraServiceInterface $compraService
    ) {}

    #[Route(name: 'app_compra_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchResult = $this->queryService->searchAndPaginate($request);
        $statistics = $this->queryService->getStatistics();

        return $this->render('compra/index.html.twig', [
            'compras' => $searchResult['pagination'],
            'totalCompras' => $statistics['totalCompras'],
            'totalPagadas' => $statistics['totalPagadas'],
            'totalPendientes' => $statistics['totalPendientes'],
            'gastosTotales' => $statistics['gastosTotales'],
            'searchTerm' => $searchResult['searchTerm'],
        ]);
    }

    #[Route('/new', name: 'app_compra_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $compra = $this->compraService->initialize();
        return $this->handleCompraForm($request, $compra, 'create');
    }

    #[Route('/new/{id}', name: 'app_compra_new_by_id', methods: ['GET', 'POST'])]
    public function newById(Request $request, Producto $producto): Response
    {
        $compra = $this->compraService->initialize($producto);
        return $this->handleCompraForm($request, $compra, 'create');
    }

    #[Route('/{id}', name: 'app_compra_show', methods: ['GET'])]
    public function show(Compra $compra): Response
    {
        return $this->render('compra/show.html.twig', [
            'compra' => $compra,
            'detalle_compras' => $compra->getDetalleCompras(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_compra_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Compra $compra): Response
    {
        // Guardar detalles originales antes del handleRequest
        $originalDetalles = new ArrayCollection();
        foreach ($compra->getDetalleCompras() as $detalle) {
            $originalDetalles->add($detalle);
        }

        return $this->handleCompraForm($request, $compra, 'edit', $originalDetalles);
    }

    #[Route('/{id}', name: 'app_compra_delete', methods: ['POST'])]
    public function delete(Request $request, Compra $compra): Response
    {
        if ($this->isCsrfTokenValid('delete' . $compra->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $this->compraService->delete($compra);
                $this->addFlash('success', 'La compra ha sido eliminada correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar la compra: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Error de seguridad. No se pudo eliminar la compra.');
        }

        return $this->redirectToRoute('app_compra_index', [], Response::HTTP_SEE_OTHER);
    }

    private function handleCompraForm(
        Request $request,
        Compra $compra,
        string $action = 'create',
        ArrayCollection $originalDetalles = null
    ): Response {
        $form = $this->createForm(CompraType::class, $compra);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($action === 'create') {
                    $this->compraService->create($compra);
                    $message = 'La compra ha sido registrada exitosamente.';
                } else {
                    $this->compraService->update($compra, $originalDetalles->toArray());
                    $message = 'La compra ha sido actualizada correctamente.';
                }

                $this->addFlash('success', $message);
                return $this->redirectToRoute('app_compra_show', ['id' => $compra->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al procesar la compra: ' . $e->getMessage());
            }
        }

        $detalleCompra = new DetalleCompra();
        $formDetalle = $this->createForm(DetalleCompraType::class, $detalleCompra);

        $template = $action === 'create' ? 'new.html.twig' : 'edit.html.twig';
        return $this->render("compra/{$template}", [
            'compra' => $compra,
            'form' => $form,
            'formDetalle' => $formDetalle,
            'detalle_compras' => $compra->getDetalleCompras(),
        ]);
    }
}
