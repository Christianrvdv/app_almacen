<?php

namespace App\Repository;

use App\Entity\Producto;
use App\Service\ProductoRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Producto>
 */
class ProductoRepository extends ServiceEntityRepository implements ProductoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Producto::class);
    }

    public function findWithRelations(int $id): ?Producto
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.categoria', 'c')
            ->addSelect('c')
            ->leftJoin('p.proveedor', 'prov')
            ->addSelect('prov')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getIngresosPorCategoria(int $categoriaId): float
    {
        $result = $this->createQueryBuilder('p')
            ->select('COALESCE(SUM(dv.subtotal), 0) as ingresos')
            ->join('p.detalleVentas', 'dv')
            ->where('p.categoria = :categoriaId')
            ->setParameter('categoriaId', $categoriaId)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) $result;
    }
    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nombre LIKE :searchTerm OR p.descripcion LIKE :searchTerm OR p.codigo_barras LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('p.fecha_actualizacion', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
