<?php

namespace App\Service\Compra;

use App\Repository\CompraRepository;
use App\Service\Compra\Interface\CompraQueryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class CompraQueryService implements CompraQueryInterface
{
    public function __construct(
        private CompraRepository $repository,
        private PaginatorInterface $paginator
    ) {}

    public function searchAndPaginate(Request $request): array
    {
        $searchTerm = $request->query->get('q', '');

        $queryBuilder = $this->repository->createQueryBuilder('c')
            ->orderBy('c.fecha', 'DESC');

        if (!empty($searchTerm)) {
            $queryBuilder
                ->andWhere('c.numero_factura LIKE :searchTerm OR c.estado LIKE :searchTerm OR c.id = :id')
                ->setParameter('searchTerm', '%' . $searchTerm . '%')
                ->setParameter('id', is_numeric($searchTerm) ? (int) $searchTerm : 0);
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
        $totalCompras = $this->repository->count([]);
        $totalPagadas = $this->repository->count(['estado' => 'pagada']);
        $totalPendientes = $this->repository->count(['estado' => 'pendiente']);

        $gastosTotales = $this->repository->createQueryBuilder('c')
            ->select('SUM(c.total)')
            ->where('c.estado = :estado')
            ->setParameter('estado', 'pagada')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return [
            'totalCompras' => $totalCompras,
            'totalPagadas' => $totalPagadas,
            'totalPendientes' => $totalPendientes,
            'gastosTotales' => $gastosTotales,
        ];
    }
}
