<?php

namespace App\Service\Cliente;

use App\Repository\ClienteRepository;
use App\Service\Cliente\Interface\ClienteQueryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class ClienteQueryService implements ClienteQueryInterface
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

    public function getStatistics(): array
    {
        $totalClientes = $this->repository->count([]);

        $totalConEmail = $this->repository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.email IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $totalConTelefono = $this->repository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.telefono IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $totalConDireccion = $this->repository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.direccion IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalClientes' => $totalClientes,
            'totalConEmail' => $totalConEmail,
            'totalConTelefono' => $totalConTelefono,
            'totalConDireccion' => $totalConDireccion,
        ];
    }
}
