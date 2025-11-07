<?php
// src/Controller/DashboardController.php

namespace App\Controller;

use App\Service\DashboardStatsInterface;
use App\Service\StockAlertInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        private DashboardStatsInterface $dashboardStats,
        private StockAlertInterface $stockAlert
    ) {}

    #[Route('/dashboard', name: 'app_dashboard_index')]
    public function index(): Response
    {
        $dashboardData = $this->dashboardStats->getDashboardData();
        $stockAlerts = $this->stockAlert->getStockAlerts();

        return $this->render('dashboard/index.html.twig', [
            'ventasHoy' => $dashboardData['ventasHoy'],
            'productosActivos' => $dashboardData['productosActivos'],
            'productosInactivos' => $dashboardData['productosInactivos'],
            'totalProductos' => $dashboardData['totalProductos'],
            'totalClientes' => $dashboardData['totalClientes'],
            'nuevosClientesMes' => $dashboardData['nuevosClientesMes'],
            'comprasMes' => $dashboardData['comprasMes'],
            'comprasPendientes' => $dashboardData['comprasPendientes'],
            'ventasMes' => $dashboardData['ventasMes'],
            'productosStockBajo' => $stockAlerts['total_stock_bajo'],
            'productosAgotados' => $stockAlerts['total_agotados'],
            'stockAlerts' => $stockAlerts,
        ]);
    }
}
