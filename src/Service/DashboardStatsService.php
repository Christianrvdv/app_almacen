<?php

namespace App\Service;

use App\Repository\ProductoRepository;
use App\Repository\VentaRepository;
use App\Repository\CompraRepository;
use App\Repository\ClienteRepository;

class DashboardStatsService implements DashboardStatsInterface
{
    public function __construct(
        private ProductoRepository $productoRepository,
        private VentaRepository $ventaRepository,
        private CompraRepository $compraRepository,
        private ClienteRepository $clienteRepository
    ) {}

    public function getDashboardData(): array
    {
        // Ventas de hoy
        $ventasHoy = $this->ventaRepository->createQueryBuilder('v')
            ->select('COUNT(v.id) as total, COALESCE(SUM(v.total), 0) as monto')
            ->where('v.fecha >= :hoy')
            ->andWhere('v.estado = :estado')
            ->setParameter('hoy', new \DateTime('today'))
            ->setParameter('estado', 'completada')
            ->getQuery()
            ->getSingleResult();

        // Productos activos e inactivos
        $productosActivosCount = $this->productoRepository->count(['activo' => true]);
        $productosInactivos = $this->productoRepository->count(['activo' => false]);
        $totalProductos = $productosActivosCount + $productosInactivos;

        // Clientes registrados
        $totalClientes = $this->clienteRepository->count([]);

        // Nuevos clientes este mes
        $primerDiaMes = new \DateTime('first day of this month');
        $nuevosClientesMes = $this->clienteRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.fecha_registro >= :mes')
            ->setParameter('mes', $primerDiaMes)
            ->getQuery()
            ->getSingleScalarResult();

        // Compras del mes
        $comprasMes = $this->compraRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.fecha >= :mes')
            ->andWhere('c.estado = :estado')
            ->setParameter('mes', $primerDiaMes)
            ->setParameter('estado', 'completada')
            ->getQuery()
            ->getSingleScalarResult();

        // Compras pendientes
        $comprasPendientes = $this->compraRepository->count(['estado' => 'pendiente']);

        // Ventas del mes
        $ventasMes = $this->ventaRepository->createQueryBuilder('v')
            ->select('COALESCE(SUM(v.total), 0)')
            ->where('v.fecha >= :mes')
            ->andWhere('v.estado = :estado')
            ->setParameter('mes', $primerDiaMes)
            ->setParameter('estado', 'completada')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'ventasHoy' => $ventasHoy,
            'productosActivos' => $productosActivosCount,
            'productosInactivos' => $productosInactivos,
            'totalProductos' => $totalProductos,
            'totalClientes' => $totalClientes,
            'nuevosClientesMes' => $nuevosClientesMes,
            'comprasMes' => $comprasMes,
            'comprasPendientes' => $comprasPendientes,
            'ventasMes' => $ventasMes,
        ];
    }
}
