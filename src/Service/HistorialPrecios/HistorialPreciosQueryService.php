<?php

namespace App\Service\HistorialPrecios;

use App\Repository\HistorialPreciosRepository;
use App\Service\HistorialPrecios\Interface\HistorialPreciosQueryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class HistorialPreciosQueryService implements HistorialPreciosQueryInterface
{
    public function __construct(
        private HistorialPreciosRepository $repository,
        private PaginatorInterface $paginator
    ) {}

    public function searchAndPaginate(Request $request): array
    {
        $searchTerm = $request->query->get('q', '');

        $queryBuilder = $this->repository->createQueryBuilder('h')
            ->leftJoin('h.producto', 'p')
            ->addSelect('p')
            ->orderBy('h.fecha_cambio', 'DESC');

        if (!empty($searchTerm)) {
            $queryBuilder
                ->andWhere('h.tipo LIKE :searchTerm OR h.motivo LIKE :searchTerm OR p.nombre LIKE :searchTerm')
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
        $totalRegistros = $this->repository->count([]);
        $totalVenta = $this->repository->count(['tipo' => 'venta']);
        $totalCompra = $this->repository->count(['tipo' => 'compra']);
        $totalAjustePromo = $this->repository->createQueryBuilder('h')
            ->select('COUNT(h.id)')
            ->where("h.tipo IN ('promocion', 'ajuste')")
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalRegistros' => $totalRegistros,
            'totalVenta' => $totalVenta,
            'totalCompra' => $totalCompra,
            'totalAjustePromo' => $totalAjustePromo,
        ];
    }

    public function findLastByProductAndType($producto, string $type): ?object
    {
        return $this->repository->findLastByProductAndType($producto, $type);
    }
}
