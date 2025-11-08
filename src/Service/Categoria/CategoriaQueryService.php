<?php

namespace App\Service\Categoria;

use App\Repository\CategoriaRepository;
use App\Service\Categoria\Interface\CategoriaQueryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class CategoriaQueryService implements CategoriaQueryInterface
{
    public function __construct(
        private CategoriaRepository $repository,
        private PaginatorInterface $paginator
    ) {}

    public function searchAndPaginate(Request $request): array
    {
        $searchTerm = $request->query->get('q', '');

        $queryBuilder = $this->repository->createQueryBuilder('c')
            ->orderBy('c.nombre', 'ASC');

        if (!empty($searchTerm)) {
            $queryBuilder
                ->andWhere('c.nombre LIKE :searchTerm OR c.descripcion LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        $query = $queryBuilder->getQuery();

        return [
            'pagination' => $this->paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                10
            ),
            'searchTerm' => $searchTerm
        ];
    }

    public function getStatistics(): array
    {
        $totalCategorias = $this->repository->count([]);

        $totalConDescripcion = $this->repository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.descripcion IS NOT NULL AND c.descripcion != :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult();

        $totalEnUso = $this->repository->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.id)')
            ->innerJoin('c.productos', 'p')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalCategorias' => $totalCategorias,
            'totalConDescripcion' => $totalConDescripcion,
            'totalEnUso' => $totalEnUso,
        ];
    }
}
