<?php

namespace App\Service;

use App\Repository\ProductoRepository;

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
