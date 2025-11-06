<?php

namespace App\Service;

use App\Repository\VentaRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class VentaSearchService implements VentaSearchInterface
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
}
