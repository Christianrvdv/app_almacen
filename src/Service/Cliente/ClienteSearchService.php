<?php

namespace App\Service\Cliente;

use App\Repository\ClienteRepository;
use App\Service\Cliente\Interface\ClienteSearchInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class ClienteSearchService implements ClienteSearchInterface
{
    public function __construct(
        private ClienteRepository $repository,
        private PaginatorInterface $paginator
    ) {}

    public function searchAndPaginate(Request $request): array
    {
        $searchTerm = $request->query->get('q', '');

        $queryBuilder = $this->repository->createQueryBuilder('c')
            ->orderBy('c.nombre', 'ASC');

        if (!empty($searchTerm)) {
            $queryBuilder
                ->andWhere('c.nombre LIKE :searchTerm OR c.email LIKE :searchTerm OR c.telefono LIKE :searchTerm OR c.direccion LIKE :searchTerm')
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
