<?php

namespace App\Repository;

use App\Entity\Producto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Producto>
 */
class ProductoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Producto::class);
    }

    public function findWithRelations(Uuid $id): ?Producto
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.categoria', 'c')
            ->addSelect('c')
            ->leftJoin('p.proveedor', 'prov')
            ->addSelect('prov')
            ->where('p.id = :id')
            ->setParameter('id', $id->toBinary())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getIngresosPorCategoria(Uuid $categoriaId): float
    {
        $result = $this->createQueryBuilder('p')
            ->select('COALESCE(SUM(dv.subtotal), 0) as ingresos')
            ->join('p.detalleVentas', 'dv')
            ->where('p.categoria = :categoriaId')
            ->setParameter('categoriaId', $categoriaId->toBinary())
            ->getQuery()
            ->getSingleScalarResult();

        return (float) $result;
    }

    /*
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.categoria', 'c')
            ->addSelect('c')
            ->leftJoin('p.proveedor', 'prov')
            ->addSelect('prov')
            ->orderBy('p.fecha_actualizacion', 'DESC')
            ->getQuery()
            ->getResult();
    }*/
}
