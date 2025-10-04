<?php
// src/Controller/DashboardController.php

namespace App\Controller;

use App\Repository\ProductoRepository;
use App\Repository\VentaRepository;
use App\Repository\CompraRepository;
use App\Repository\ClienteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard_index')]
    public function index(
        ProductoRepository     $productoRepository,
        VentaRepository        $ventaRepository,
        CompraRepository       $compraRepository,
        ClienteRepository      $clienteRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $connection = $entityManager->getConnection();

        // Ventas de hoy
        $ventasHoy = $ventaRepository->createQueryBuilder('v')
            ->select('COUNT(v.id) as total, COALESCE(SUM(v.total), 0) as monto')
            ->where('v.fecha >= :hoy')
            ->andWhere('v.estado = :estado')
            ->setParameter('hoy', new \DateTime('today'))
            ->setParameter('estado', 'completada')
            ->getQuery()
            ->getSingleResult();

        // Productos activos e inactivos
        $productosActivos = $productoRepository->count(['activo' => true]);
        $productosInactivos = $productoRepository->count(['activo' => false]);
        $totalProductos = $productosActivos + $productosInactivos;

        // Clientes registrados
        $totalClientes = $clienteRepository->count([]);

        // Nuevos clientes este mes
        $primerDiaMes = new \DateTime('first day of this month');
        $nuevosClientesMes = $clienteRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.fecha_registro >= :mes')
            ->setParameter('mes', $primerDiaMes)
            ->getQuery()
            ->getSingleScalarResult();

        // Compras del mes
        $comprasMes = $compraRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.fecha >= :mes')
            ->andWhere('c.estado = :estado')
            ->setParameter('mes', $primerDiaMes)
            ->setParameter('estado', 'completada')
            ->getQuery()
            ->getSingleScalarResult();

        // Compras pendientes
        $comprasPendientes = $compraRepository->count(['estado' => 'pendiente']);

        // Productos con stock bajo (usando consulta nativa para mejor performance)
        $sqlStockBajo = "
                SELECT COUNT(*) as total
                FROM producto p
                WHERE p.activo = true
                AND p.stock_minimo > 0
                AND (
                    SELECT COALESCE(SUM(dc.cantidad), 0) - COALESCE(SUM(dv.cantidad), 0)
                    FROM producto p2
                    LEFT JOIN detalle_compra dc ON p2.id = dc.producto_id
                    LEFT JOIN compra c ON dc.compra_id = c.id AND c.estado = 'completada'
                    LEFT JOIN detalle_venta dv ON p2.id = dv.producto_id
                    LEFT JOIN venta v ON dv.venta_id = v.id AND v.estado = 'completada'
                    WHERE p2.id = p.id
                ) <= p.stock_minimo
            ";
        $productosStockBajo = $connection->executeQuery($sqlStockBajo)->fetchOne();

        // Productos agotados
        $sqlAgotados = "
                SELECT COUNT(*) as total
                FROM producto p
                WHERE p.activo = true
                AND (
                    SELECT COALESCE(SUM(dc.cantidad), 0) - COALESCE(SUM(dv.cantidad), 0)
                    FROM producto p2
                    LEFT JOIN detalle_compra dc ON p2.id = dc.producto_id
                    LEFT JOIN compra c ON dc.compra_id = c.id AND c.estado = 'completada'
                    LEFT JOIN detalle_venta dv ON p2.id = dv.producto_id
                    LEFT JOIN venta v ON dv.venta_id = v.id AND v.estado = 'completada'
                    WHERE p2.id = p.id
                ) <= 0
            ";
        $productosAgotados = $connection->executeQuery($sqlAgotados)->fetchOne();

        // Ventas del mes
        $ventasMes = $ventaRepository->createQueryBuilder('v')
            ->select('COALESCE(SUM(v.total), 0)')
            ->where('v.fecha >= :mes')
            ->andWhere('v.estado = :estado')
            ->setParameter('mes', $primerDiaMes)
            ->setParameter('estado', 'completada')
            ->getQuery()
            ->getSingleScalarResult();


        return $this->render('dashboard/index.html.twig', [
            'ventasHoy' => $ventasHoy,
            'productosActivos' => $productosActivos,
            'productosInactivos' => $productosInactivos,
            'totalProductos' => $totalProductos,
            'totalClientes' => $totalClientes,
            'nuevosClientesMes' => $nuevosClientesMes,
            'comprasMes' => $comprasMes,
            'comprasPendientes' => $comprasPendientes,
            'productosStockBajo' => $productosStockBajo,
            'productosAgotados' => $productosAgotados,
            'ventasMes' => $ventasMes,
        ]);
    }
}
