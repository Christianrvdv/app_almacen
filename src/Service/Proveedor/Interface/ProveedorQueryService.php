<?php

namespace App\Service\Proveedor;

use App\Repository\ProveedorRepository;
use App\Service\Proveedor\Interface\ProveedorQueryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class ProveedorQueryService implements ProveedorQueryInterface
{
    public function __construct(
        private ProveedorRepository $repository,
        private PaginatorInterface $paginator
    ) {}

    public function searchAndPaginate(Request $request): array
    {
        $searchTerm = $request->query->get('q', '');

        $queryBuilder = $this->repository->createQueryBuilder('p')
            ->orderBy('p.nombre', 'ASC');

        if (!empty($searchTerm)) {
            $queryBuilder
                ->andWhere('p.nombre LIKE :searchTerm OR p.telefono LIKE :searchTerm OR p.email LIKE :searchTerm OR p.direccion LIKE :searchTerm')
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
        $totalProveedores = $this->repository->count([]);
        $totalConTelefono = $this->repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.telefono IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
        $totalConEmail = $this->repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.email IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
        $totalConDireccion = $this->repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.direccion IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalProveedores' => $totalProveedores,
            'totalConTelefono' => $totalConTelefono,
            'totalConEmail' => $totalConEmail,
            'totalConDireccion' => $totalConDireccion,
        ];
    }
}
