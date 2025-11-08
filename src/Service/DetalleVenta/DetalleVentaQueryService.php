<?php

namespace App\Service\DetalleVenta;

use App\Repository\DetalleVentaRepository;
use App\Service\DetalleVenta\Interface\DetalleVentaQueryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class DetalleVentaQueryService implements DetalleVentaQueryInterface
{
    public function __construct(
        private DetalleVentaRepository $repository,
        private PaginatorInterface $paginator
    ) {}

    public function searchAndPaginate(Request $request): array
    {
        $searchTerm = $request->query->get('q', '');

        $queryBuilder = $this->repository->createQueryBuilder('dv')
            ->leftJoin('dv.venta', 'v')
            ->addSelect('v')
            ->leftJoin('dv.producto', 'p')
            ->addSelect('p')
            ->orderBy('dv.id', 'DESC');

        if (!empty($searchTerm)) {
            $queryBuilder
                ->andWhere('p.nombre LIKE :searchTerm OR v.id LIKE :searchTerm')
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
        $totalDetalles = $this->repository->count([]);

        $totalVentasConProducto = $this->repository->createQueryBuilder('dv')
            ->select('COUNT(DISTINCT dv.venta)')
            ->where('dv.producto IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $ingresosTotales = $this->repository->createQueryBuilder('dv')
            ->select('COALESCE(SUM(dv.subtotal), 0)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalDetalles' => $totalDetalles,
            'totalVentasConProducto' => $totalVentasConProducto,
            'ingresosTotales' => $ingresosTotales,
        ];
    }
}
