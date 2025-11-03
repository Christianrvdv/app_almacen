<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Form\CategoriaType;
use App\Repository\CategoriaRepository;
use App\Repository\ProductoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categoria')]
final class CategoriaController extends AbstractController
{
    #[Route(name: 'app_categoria_index', methods: ['GET'])]
    public function index(Request $request, CategoriaRepository $categoriaRepository, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('q', ''); // Obtener término de búsqueda

        // Construir query con filtro de búsqueda si existe
        $queryBuilder = $categoriaRepository->createQueryBuilder('c')
            ->orderBy('c.nombre', 'ASC');

        // Aplicar filtro de búsqueda si hay término
        if (!empty($searchTerm)) {
            $queryBuilder
                ->andWhere('c.nombre LIKE :searchTerm OR c.descripcion LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        $query = $queryBuilder->getQuery();

        $categorias = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        // Estadísticas totales (sin filtro de búsqueda para mantener precisión)
        $totalCategorias = $categoriaRepository->count([]);
        $totalConDescripcion = $categoriaRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.descripcion IS NOT NULL AND c.descripcion != :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult();
        $totalEnUso = $categoriaRepository->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.id)')
            ->innerJoin('c.productos', 'p')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('categoria/index.html.twig', [
            'categorias' => $categorias,
            'totalCategorias' => $totalCategorias,
            'totalConDescripcion' => $totalConDescripcion,
            'totalEnUso' => $totalEnUso,
            'searchTerm' => $searchTerm, // Pasar el término actual
        ]);
    }

    #[Route('/new', name: 'app_categoria_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categoria = new Categoria();
        $form = $this->createForm(CategoriaType::class, $categoria);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categoria);
            $entityManager->flush();

            $this->addFlash('success', 'La categoría ha sido creada correctamente.');

            return $this->redirectToRoute('app_categoria_index', [], Response::HTTP_SEE_OTHER);
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
    public function edit(Request $request, Categoria $categoria, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoriaType::class, $categoria);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La categoría ha sido actualizada correctamente.');

            return $this->redirectToRoute('app_categoria_show', [
                'id' => $categoria->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categoria/edit.html.twig', [
            'categoria' => $categoria,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categoria_delete', methods: ['POST'])]
    public function delete(Request $request, Categoria $categoria, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $categoria->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($categoria);
            $entityManager->flush();
            $this->addFlash('success', 'La categoría ha sido eliminada correctamente.');
        } else {
            $this->addFlash('error', 'Error de seguridad. No se pudo eliminar la categoría.');
        }

        return $this->redirectToRoute('app_categoria_index', [], Response::HTTP_SEE_OTHER);
    }
}
