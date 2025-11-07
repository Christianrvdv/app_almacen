<?php

namespace App\Service\AjusteInventario;

use App\Repository\AjusteInventarioRepository;
use App\Service\AjusteInventario\Interface\AjusteInventarioQueryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class AjusteInventarioQueryService implements AjusteInventarioQueryInterface
{
    public function __construct(
        private AjusteInventarioRepository $repository,
        private PaginatorInterface $paginator
    ) {}

    public function searchAndPaginate(Request $request): array
    {
        $searchTerm = $request->query->get('q', '');

        $queryBuilder = $this->repository->createQueryBuilder('a')
            ->leftJoin('a.producto', 'p')
            ->addSelect('p')
            ->orderBy('a.fecha', 'DESC');

        if (!empty($searchTerm)) {
            $queryBuilder
                ->andWhere('a.motivo LIKE :searchTerm OR a.tipo LIKE :searchTerm OR a.usuario LIKE :searchTerm OR p.nombre LIKE :searchTerm')
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
        return [
            'totalAjustes' => $this->repository->count([]),
            'totalEntradas' => $this->repository->count(['tipo' => 'entrada']),
            'totalSalidas' => $this->repository->count(['tipo' => 'salida']),
            'cantidadUsuariosUnicos' => $this->getUniqueUsersCount()
        ];
    }

    private function getUniqueUsersCount(): int
    {
        return $this->repository->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.usuario)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
