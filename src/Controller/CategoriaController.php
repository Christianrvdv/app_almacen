<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Form\CategoriaType;
use App\Repository\ProductoRepository;
use App\Service\Categoria\Interface\CategoriaOperationsInterface;
use App\Service\Categoria\Interface\CategoriaSearchInterface;
use App\Service\Categoria\Interface\CategoriaStatsInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categoria')]
final class CategoriaController extends AbstractController
{
    public function __construct(
        private CategoriaSearchInterface $searchService,
        private CategoriaStatsInterface $statsService,
        private CategoriaOperationsInterface $operationsService
    ) {}

    #[Route(name: 'app_categoria_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchResult = $this->searchService->searchAndPaginate($request);
        $statistics = $this->statsService->getStatistics();

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
        $form = $this->createForm(CategoriaType::class, $categoria);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operationsService->createCategoria($categoria);
                $this->addFlash('success', 'La categoría ha sido creada correctamente.');
                return $this->redirectToRoute('app_categoria_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al crear la categoría: ' . $e->getMessage());
            }
        }

        return $this->render('categoria/new.html.twig', [
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

    #[Route('/{id}/edit', name: 'app_categoria_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categoria $categoria): Response
    {
        $form = $this->createForm(CategoriaType::class, $categoria);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operationsService->updateCategoria($categoria);
                $this->addFlash('success', 'La categoría ha sido actualizada correctamente.');
                return $this->redirectToRoute('app_categoria_show', ['id' => $categoria->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al actualizar la categoría: ' . $e->getMessage());
            }
        }

        return $this->render('categoria/edit.html.twig', [
            'categoria' => $categoria,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categoria_delete', methods: ['POST'])]
    public function delete(Request $request, Categoria $categoria): Response
    {
        if ($this->isCsrfTokenValid('delete' . $categoria->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $this->operationsService->deleteCategoria($categoria);
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
