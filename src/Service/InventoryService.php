<?php
// src/Service/InventoryService.php

namespace App\Service;

use App\Entity\Producto;

class InventoryService
{
    public function calculateProductStats(Producto $producto): array
    {
        $detallesVentas = $producto->getDetalleVentas();
        $detallesCompras = $producto->getDetalleCompras();
        $ajustesInventario = $producto->getAjusteInventarios();

        $stock = 0;
        $ingresos = 0;
        $ventas = 0;

        // Calcular stock a partir de compras
        foreach ($detallesCompras as $detalleCompra) {
            $stock += $detalleCompra->getCantidad();
        }

        // Ajustar stock con ventas y calcular ingresos
        foreach ($detallesVentas as $detalleVenta) {
            $stock -= $detalleVenta->getCantidad();
            $ventas += $detalleVenta->getCantidad();
            $ingresos += ($detalleVenta->getPrecioUnitario() * $detalleVenta->getCantidad());
        }

        // Ajustar stock con ajustes de inventario
        foreach ($ajustesInventario as $ajusteInventario) {
            if ($ajusteInventario->getTipo() == "salida") {
                $stock -= $ajusteInventario->getCantidad();
            } else {
                $stock += $ajusteInventario->getCantidad();
            }
        }

        // Calcular margen
        $margen = $this->calculateMargin(
            $producto->getPrecioVentaActual(),
            $producto->getPrecioCompra()
        );

        return [
            'stock' => max(0, $stock),
            'ingresos' => $ingresos,
            'ventas' => $ventas,
            'margen' => $margen,
            'modificaciones' => $producto->getHistorialPrecios()->count(),
        ];
    }

    public function calculateMargin(?float $precioVenta, ?float $precioCompra): float
    {
        if (!$precioCompra || $precioCompra <= 0) {
            return 0;
        }

        if (!$precioVenta || $precioVenta <= $precioCompra) {
            return 0;
        }

        return (($precioVenta - $precioCompra) / $precioCompra) * 100;
    }

    public function getStockAlerts(array $productos): array
    {
        $alerts = [
            'agotados' => [],
            'stock_bajo' => [],
            'total_agotados' => 0,
            'total_stock_bajo' => 0,
        ];

        foreach ($productos as $producto) {
            // CORREGIDO: usar isActivo() en lugar de getActivo()
            if (!$producto->isActivo()) {
                continue;
            }

            $stats = $this->calculateProductStats($producto);
            $stockActual = $stats['stock'];
            $stockMinimo = $producto->getStockMinimo();

            if ($stockActual <= 0) {
                $alerts['agotados'][] = [
                    'producto' => $producto,
                    'stock_actual' => $stockActual,
                    'nombre' => $producto->getNombre(),
                ];
                $alerts['total_agotados']++;
            } elseif ($stockMinimo > 0 && $stockActual <= $stockMinimo) {
                $alerts['stock_bajo'][] = [
                    'producto' => $producto,
                    'stock_actual' => $stockActual,
                    'stock_minimo' => $stockMinimo,
                    'nombre' => $producto->getNombre(),
                ];
                $alerts['total_stock_bajo']++;
            }
        }

        return $alerts;
    }

    public function calculateInventoryValue(array $productos): float
    {
        $totalValue = 0;

        foreach ($productos as $producto) {
            // CORREGIDO: usar isActivo() en lugar de getActivo()
            if (!$producto->isActivo()) {
                continue;
            }

            $stats = $this->calculateProductStats($producto);
            $stock = $stats['stock'];
            $precioCompra = $producto->getPrecioCompra();

            if ($stock > 0 && $precioCompra > 0) {
                $totalValue += ($stock * $precioCompra);
            }
        }

        return $totalValue;
    }

    public function getInventoryTurnover(array $productos, array $ventasPeriodo = []): array
    {
        $totalCostoVentas = 0;
        $totalInventarioPromedio = 0;
        $productosConMovimiento = 0;

        foreach ($productos as $producto) {
            // CORREGIDO: usar isActivo() en lugar de getActivo()
            if (!$producto->isActivo()) {
                continue;
            }

            $stats = $this->calculateProductStats($producto);
            $costoVentas = $stats['ventas'] * $producto->getPrecioCompra();
            $inventarioPromedio = ($producto->getPrecioCompra() * $stats['stock']) / 2;

            if ($costoVentas > 0) {
                $totalCostoVentas += $costoVentas;
                $totalInventarioPromedio += $inventarioPromedio;
                $productosConMovimiento++;
            }
        }

        $rotacion = $totalInventarioPromedio > 0 ?
            ($totalCostoVentas / $totalInventarioPromedio) : 0;

        return [
            'rotacion' => round($rotacion, 2),
            'costo_ventas' => $totalCostoVentas,
            'inventario_promedio' => $totalInventarioPromedio,
            'productos_con_movimiento' => $productosConMovimiento,
        ];
    }

    /**
     * Método adicional para obtener resumen rápido de inventario
     */
    public function getInventorySummary(array $productos): array
    {
        $totalValor = 0;
        $totalProductos = 0;
        $productosActivos = 0;

        foreach ($productos as $producto) {
            if ($producto->isActivo()) {
                $productosActivos++;
                $stats = $this->calculateProductStats($producto);
                $totalValor += $stats['stock'] * $producto->getPrecioCompra();
                $totalProductos += $stats['stock'];
            }
        }

        $alerts = $this->getStockAlerts($productos);

        return [
            'valor_total' => $totalValor,
            'total_productos' => $totalProductos,
            'productos_activos' => $productosActivos,
            'productos_agotados' => $alerts['total_agotados'],
            'productos_stock_bajo' => $alerts['total_stock_bajo'],
        ];
    }
}
