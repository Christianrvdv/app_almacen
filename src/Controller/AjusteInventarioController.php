<?php

namespace App\Controller;

use App\Entity\AjusteInventario;
use App\Entity\Producto;
use App\Form\AjusteInventarioType;
use App\Service\AjusteInventario\Interface\AjusteInventarioServiceInterface;
use App\Service\AjusteInventario\Interface\AjusteInventarioQueryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ajuste/inventario')]
final class AjusteInventarioController extends AbstractController
{
    public function __construct(
        private AjusteInventarioQueryInterface $queryService,
        private AjusteInventarioServiceInterface $operationsService
    ) {}

    #[Route(name: 'app_ajuste_inventario_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchResult = $this->queryService->searchAndPaginate($request);
        $statistics = $this->queryService->getStatistics();

        return $this->render('ajuste_inventario/index.html.twig', [
            'ajuste_inventarios' => $searchResult['pagination'],
            'totalAjustes' => $statistics['totalAjustes'],
            'totalEntradas' => $statistics['totalEntradas'],
            'totalSalidas' => $statistics['totalSalidas'],
            'cantidad_usuarios_unicos' => $statistics['cantidadUsuariosUnicos'],
            'searchTerm' => $searchResult['searchTerm'],
        ]);
    }

    #[Route('/new', name: 'app_ajuste_inventario_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $ajusteInventario = new AjusteInventario();
        return $this->handleAjusteForm($request, $ajusteInventario, 'create');
    }

    #[Route('/new/{id}', name: 'app_ajuste_inventario_new_by_id', methods: ['GET', 'POST'])]
    public function newById(Request $request, Producto $producto): Response
    {
        $ajusteInventario = new AjusteInventario();
        $ajusteInventario->setProducto($producto);
        return $this->handleAjusteForm($request, $ajusteInventario, 'create');
    }

    #[Route('/{id}/edit', name: 'app_ajuste_inventario_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AjusteInventario $ajusteInventario): Response
    {
        return $this->handleAjusteForm($request, $ajusteInventario, 'update');
    }

    private function handleAjusteForm(Request $request, AjusteInventario $ajusteInventario, string $operation): Response
    {
        $form = $this->createForm(AjusteInventarioType::class, $ajusteInventario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($operation === 'create') {
                    $this->operationsService->create($ajusteInventario);
                    $message = 'El ajuste de inventario ha sido creado correctamente.';
                    $redirectRoute = 'app_ajuste_inventario_index';
                } else {
                    $this->operationsService->update($ajusteInventario);
                    $message = 'El ajuste de inventario ha sido actualizado correctamente.';
                    $redirectRoute = 'app_ajuste_inventario_show';
                }

                $this->addFlash('success', $message);
                return $this->redirectToRoute(
                    $redirectRoute,
                    $operation === 'update' ? ['id' => $ajusteInventario->getId()] : [],
                    Response::HTTP_SEE_OTHER
                );
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al procesar el ajuste: ' . $e->getMessage());
            }
        }

        $template = $operation === 'create' ? 'new.html.twig' : 'edit.html.twig';
        return $this->render("ajuste_inventario/{$template}", [
            'ajuste_inventario' => $ajusteInventario,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ajuste_inventario_delete', methods: ['POST'])]
    public function delete(Request $request, AjusteInventario $ajusteInventario): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ajusteInventario->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $this->operationsService->delete($ajusteInventario);
                $this->addFlash('success', 'El ajuste de inventario ha sido eliminado correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar el ajuste: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Error de seguridad. No se pudo eliminar el ajuste de inventario.');
        }

        return $this->redirectToRoute('app_ajuste_inventario_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_ajuste_inventario_show', methods: ['GET'])]
    public function show(AjusteInventario $ajusteInventario): Response
    {
        return $this->render('ajuste_inventario/show.html.twig', [
            'ajuste_inventario' => $ajusteInventario,
        ]);
    }
}
