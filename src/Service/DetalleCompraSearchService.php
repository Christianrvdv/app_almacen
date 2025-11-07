<?php

namespace App\Service;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class DetalleCompraSearchService implements DetalleCompraSearchInterface
{
    public function __construct(
        private DetalleCompraRepositoryInterface $repository,
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
}
