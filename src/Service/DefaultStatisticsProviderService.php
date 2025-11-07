<?php
// src/Service/DefaultStatisticsProviderService.php

namespace App\Service;

class DefaultStatisticsProviderService implements DefaultStatisticsProviderInterface
{
    public function getDefaultStatistics(): array
    {
        return $this->initializeDefaultStats();
    }

    private function initializeDefaultStats(): array
    {
        $eficienciaInventario = [
            'total_productos' => 0,
            'agotados' => 0,
            'stock_bajo' => 0,
            'stock_optimo' => 0,
            'porcentaje_optimo' => 0
        ];

        $tendenciasPrecios = [
            'total_cambios' => 0,
            'avg_incremento_venta' => 0,
            'avg_incremento_compra' => 0,
            'ultimo_cambio' => null
        ];

        $rotacionProductos = [
            'productos_activos' => 0,
            'productos_vendidos' => 0,
            'tasa_rotacion' => 0,
            'ventas_promedio_por_producto' => 0
        ];

        $metricasClientes = [
            'total_clientes' => 0,
            'clientes_recurrentes' => 0,
            'tasa_recurrencia' => 0,
            'promedio_ventas_por_cliente' => 0
        ];

        return [
            // Métricas principales
            'gananciasBrutas' => 0,
            'gastosBrutos' => 0,
            'dineroActual' => 0,
            'dineroPendiente' => 0,
            'valorInventario' => 0,
            'margenBrutoPromedio' => 0,
            'ticketPromedio' => 0,
            'productosAgotados' => 0,
            'productosStockBajo' => 0,

            // Rankings
            'topProductosRentables' => [],
            'topClientes' => [],
            'topProveedores' => [],

            // Datos para gráficas
            'ventasDiarias' => [],
            'comprasDiarias' => [],

            // Métricas adicionales (estructura simplificada)
            'metricasAdicionales' => [
                'total_productos' => 0,
                'total_ventas' => 0,
                'total_compras' => 0,
                'total_clientes' => 0,
                'productos_analizados_margen' => 0,
                'productos_problema_margen' => 0,
            ],

            // Categorías
            'ventasPorCategoria' => [],
            'margenPorCategoria' => [],

            // Métricas avanzadas
            'eficienciaInventario' => $eficienciaInventario,
            'tendenciasPrecios' => $tendenciasPrecios,
            'rotacionProductos' => $rotacionProductos,
            'metricasClientes' => $metricasClientes,
            'productosMejorRendimiento' => [],
        ];
    }
}
