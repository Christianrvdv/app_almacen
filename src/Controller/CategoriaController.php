<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Form\CategoriaType;
use App\Repository\ProductoRepository;
use App\Service\Categoria\Interface\CategoriaQueryInterface;
use App\Service\Categoria\Interface\CategoriaServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categoria')]
final class CategoriaController extends AbstractController
{
    public function __construct(
        private CategoriaQueryInterface $queryService,
        private CategoriaServiceInterface $categoriaService
    ) {}

    #[Route(name: 'app_categoria_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchResult = $this->queryService->searchAndPaginate($request);
        $statistics = $this->queryService->getStatistics();

        return $this->render('categoria/index.html.twig', [
            'categorias' => $searchResult['pagination'],
            'totalCategorias' => $statistics['totalCategorias'],
            'totalConDescripcion' => $statistics['totalConDescripcion'],
            'totalEnUso' => $statistics['totalEnUso'],
            'searchTerm' => $searchResult['searchTerm'],
        ]);
    }

    #[Route('/new', name: 'app_categoria_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $categoria = new Categoria();
        return $this->handleCategoriaForm($request, $categoria, 'create');
    }

    #[Route('/{id}/edit', name: 'app_categoria_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categoria $categoria): Response
    {
        return $this->handleCategoriaForm($request, $categoria, 'update');
    }

    private function handleCategoriaForm(Request $request, Categoria $categoria, string $operation): Response
    {
        $form = $this->createForm(CategoriaType::class, $categoria);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($operation === 'create') {
                    $this->categoriaService->create($categoria); // ← CORREGIDO
                    $message = 'La categoría ha sido creada correctamente.';
                    $redirectRoute = 'app_categoria_index';
                } else {
                    $this->categoriaService->update($categoria); // ← CORREGIDO
                    $message = 'La categoría ha sido actualizada correctamente.';
                    $redirectRoute = 'app_categoria_show';
                }

                $this->addFlash('success', $message);
                return $this->redirectToRoute(
                    $redirectRoute,
                    $operation === 'update' ? ['id' => $categoria->getId()] : [],
                    Response::HTTP_SEE_OTHER
                );
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al procesar la categoría: ' . $e->getMessage());
            }
        }

        $template = $operation === 'create' ? 'new.html.twig' : 'edit.html.twig';
        return $this->render("categoria/{$template}", [
            'categoria' => $categoria,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categoria_show', methods: ['GET'])]
    public function show(Categoria $categoria, ProductoRepository $productoRepository): Response
    {
        $ingresos = $productoRepository->getIngresosPorCategoria($categoria->getId());

        return $this->render('categoria/show.html.twig', [
            'categoria' => $categoria,
            'ingresos' => $ingresos,
        ]);
    }

    #[Route('/{id}', name: 'app_categoria_delete', methods: ['POST'])]
    public function delete(Request $request, Categoria $categoria): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categoria->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $this->categoriaService->delete($categoria); // ← CORREGIDO
                $this->addFlash('success', 'La categoría ha sido eliminada correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar la categoría: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Error de seguridad. No se pudo eliminar la categoría.');
        }

        return $this->redirectToRoute('app_categoria_index', [], Response::HTTP_SEE_OTHER);
    }
}
