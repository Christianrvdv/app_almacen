<?php
// src/Controller/DashboardController.php

namespace App\Controller;

use App\Repository\ProductoRepository;
use App\Repository\VentaRepository;
use App\Repository\CompraRepository;
use App\Repository\ClienteRepository;
use App\Service\InventoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    #[Route('/dashboard', name: 'app_dashboard_index')]
    public function index(
        ProductoRepository $productoRepository,
        VentaRepository $ventaRepository,
        CompraRepository $compraRepository,
        ClienteRepository $clienteRepository
    ): Response
    {
        // Obtener solo productos activos para las alertas de stock
        $productosActivos = $productoRepository->findBy(['activo' => true]);
        $stockAlerts = $this->inventoryService->getStockAlerts($productosActivos);

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
        $productosActivosCount = $productoRepository->count(['activo' => true]);
        $productosInactivos = $productoRepository->count(['activo' => false]);
        $totalProductos = $productosActivosCount + $productosInactivos;

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
            'productosActivos' => $productosActivosCount,
            'productosInactivos' => $productosInactivos,
            'totalProductos' => $totalProductos,
            'totalClientes' => $totalClientes,
            'nuevosClientesMes' => $nuevosClientesMes,
            'comprasMes' => $comprasMes,
            'comprasPendientes' => $comprasPendientes,
            'productosStockBajo' => $stockAlerts['total_stock_bajo'],
            'productosAgotados' => $stockAlerts['total_agotados'],
            'ventasMes' => $ventasMes,
            'stockAlerts' => $stockAlerts, // Enviar alertas completas si las necesitas en el template
        ]);
    }
}
