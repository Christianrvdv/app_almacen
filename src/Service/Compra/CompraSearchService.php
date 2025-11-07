<?php

namespace App\Service\Compra;

use App\Repository\CompraRepository;
use App\Service\Compra\Interface\CompraSearchInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class CompraSearchService implements CompraSearchInterface
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
}
