<?php

namespace App\Service\Dashboard;

use App\Repository\ProductoRepository;
use App\Service\Core\InventoryService;
use App\Service\Dashboard\Interface\StockAlertInterface;

class StockAlertService implements StockAlertInterface
{
    public function __construct(
        private InventoryService $inventoryService,
        private ProductoRepository $productoRepository
    ) {}

    public function getStockAlerts(): array
    {
        $productosActivos = $this->productoRepository->findBy(['activo' => true]);
        return $this->inventoryService->getStockAlerts($productosActivos);
    }
}
