<?php

namespace App\Service\DetalleCompra;

use App\Repository\DetalleCompraRepository;
use App\Service\DetalleCompra\Interface\DetalleCompraQueryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class DetalleCompraQueryService implements DetalleCompraQueryInterface
{
    public function __construct(
        private DetalleCompraRepository $repository,
        private PaginatorInterface $paginator
    ) {}

    public function searchAndPaginate(Request $request): array
    {
        $searchTerm = $request->query->get('q', '');

        $queryBuilder = $this->repository->createQueryBuilder('d')
            ->leftJoin('d.compra', 'c')
            ->leftJoin('d.producto', 'p')
            ->orderBy('d.id', 'DESC');

        if (!empty($searchTerm)) {
            $queryBuilder
                ->andWhere('p.nombre LIKE :searchTerm OR c.codigo LIKE :searchTerm')
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

        $totalConProducto = $this->repository->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.producto IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $sumaSubtotal = $this->repository->createQueryBuilder('d')
            ->select('SUM(d.subtotal)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalDetalles' => $totalDetalles,
            'totalConProducto' => $totalConProducto,
            'sumaSubtotal' => $sumaSubtotal ?? 0,
        ];
    }
}
