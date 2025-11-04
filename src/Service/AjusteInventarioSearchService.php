<?php

namespace App\Service;

use App\Repository\AjusteInventarioRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class AjusteInventarioSearchService
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
}
