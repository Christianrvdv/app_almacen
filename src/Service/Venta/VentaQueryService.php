<?php

namespace App\Service\Venta;

use App\Repository\VentaRepository;
use App\Service\Venta\Interface\VentaQueryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class VentaQueryService implements VentaQueryInterface
{
    public function __construct(
        private VentaRepository $repository,
        private PaginatorInterface $paginator
    ) {}

    public function searchAndPaginate(Request $request): array
    {
        $searchTerm = $request->query->get('q', '');

        $queryBuilder = $this->repository->createQueryBuilder('v')
            ->leftJoin('v.cliente', 'c')
            ->addSelect('c')
            ->orderBy('v.fecha', 'DESC');

        if (!empty($searchTerm)) {
            if (is_numeric($searchTerm)) {
                $queryBuilder
                    ->andWhere('v.id = :id OR v.estado LIKE :searchTerm OR v.total LIKE :searchTerm OR v.tipo_venta LIKE :searchTerm OR c.nombre LIKE :searchTerm')
                    ->setParameter('id', (int) $searchTerm)
                    ->setParameter('searchTerm', '%' . $searchTerm . '%');
            } else {
                $queryBuilder
                    ->andWhere('v.estado LIKE :searchTerm OR v.tipo_venta LIKE :searchTerm OR c.nombre LIKE :searchTerm')
                    ->setParameter('searchTerm', '%' . $searchTerm . '%');
            }
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
        $totalVentas = $this->repository->count([]);
        $totalCompletadas = $this->repository->count(['estado' => 'completada']);
        $totalPendientes = $this->repository->count(['estado' => 'pendiente']);

        $totalIngresos = $this->repository->createQueryBuilder('v')
            ->select('SUM(v.total)')
            ->where('v.estado = :estado')
            ->setParameter('estado', 'completada')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return [
            'totalVentas' => $totalVentas,
            'totalCompletadas' => $totalCompletadas,
            'totalPendientes' => $totalPendientes,
            'totalIngresos' => $totalIngresos,
        ];
    }
}
