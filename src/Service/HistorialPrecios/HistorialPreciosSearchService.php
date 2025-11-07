<?php

namespace App\Service\HistorialPrecios;

use App\Repository\HistorialPreciosRepository;
use App\Service\HistorialPrecios\Interface\HistorialPreciosSearchInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class HistorialPreciosSearchService implements HistorialPreciosSearchInterface
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
}
