<?php

namespace App\Service\Producto;

use App\Repository\ProductoRepository;
use App\Service\Producto\Interface\ProductoQueryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductoQueryService implements ProductoQueryInterface
{
    public function __construct(
        private ProductoRepository $repository,
        private PaginatorInterface $paginator
    ) {}

    public function searchAndPaginate(Request $request): array
    {
        $searchTerm = $request->query->get('q', '');

        $queryBuilder = $this->repository->createQueryBuilder('p')
            ->leftJoin('p.categoria', 'c')
            ->addSelect('c')
            ->leftJoin('p.proveedor', 'prov')
            ->addSelect('prov')
            ->orderBy('p.fecha_actualizacion', 'DESC');

        if (!empty($searchTerm)) {
            $queryBuilder
                ->andWhere('p.nombre LIKE :searchTerm OR p.descripcion LIKE :searchTerm OR p.codigo_barras LIKE :searchTerm')
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
        $totalProductos = $this->repository->count([]);
        $totalActivos = $this->repository->count(['activo' => true]);
        $totalInactivos = $this->repository->count(['activo' => false]);
        $totalConCategoria = $this->repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.categoria IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalProductos' => $totalProductos,
            'totalActivos' => $totalActivos,
            'totalInactivos' => $totalInactivos,
            'totalConCategoria' => $totalConCategoria,
        ];
    }
}
