<?php

namespace App\Service\Categoria;

use App\Repository\CategoriaRepository;
use App\Service\Categoria\Interface\CategoriaSearchInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class CategoriaSearchService implements CategoriaSearchInterface
{
    public function __construct(
        private CategoriaRepository $repository,
        private PaginatorInterface $paginator
    ) {}

    public function searchAndPaginate(Request $request): array
    {
        $searchTerm = $request->query->get('q', '');

        $queryBuilder = $this->repository->createQueryBuilder('c')
            ->orderBy('c.nombre', 'ASC');

        if (!empty($searchTerm)) {
            $queryBuilder
                ->andWhere('c.nombre LIKE :searchTerm OR c.descripcion LIKE :searchTerm')
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
