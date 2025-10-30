<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class  StatisticsService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getDashboardStatistics(string $filtro = 'mes_actual', string $fechaEspecifica = null): array
    {
        $connection = $this->entityManager->getConnection();
        $condicionesFecha = $this->construirCondicionesFecha($filtro, $fechaEspecifica ?? date('Y-m-d'));

        try {
            return [
                'gananciasBrutas' => $this->getGananciasBrutas($condicionesFecha),
                'gastosBrutos' => $this->getGastosBrutos($condicionesFecha),
                'dineroPendiente' => $this->getDineroPendiente($condicionesFecha),
                'dineroActual' => $this->getDineroActual($condicionesFecha),
                'valorInventario' => $this->getValorInventario(),
                'margenBrutoPromedio' => $this->getMargenBrutoPromedio(),
                'ticketPromedio' => $this->getTicketPromedio($condicionesFecha),
                'productosAgotados' => $this->getProductosAgotados(),
                'productosStockBajo' => $this->getProductosStockBajo(),
                'topProductosRentables' => $this->getTopProductosRentables(),
                'topClientes' => $this->getTopClientes($condicionesFecha),
                'topProveedores' => $this->getTopProveedores($condicionesFecha),
                'ventasDiarias' => $this->getVentasDiarias($condicionesFecha),
                'comprasDiarias' => $this->getComprasDiarias($condicionesFecha),
                'metricasAdicionales' => $this->getMetricasAdicionales($condicionesFecha),
                'ventasPorCategoria' => $this->getVentasPorCategoria($condicionesFecha),
                'eficienciaInventario' => $this->getEficienciaInventario(),
                'tendenciasPrecios' => $this->getTendenciasPrecios($condicionesFecha),
                'rotacionProductos' => $this->getRotacionProductos($condicionesFecha),
                'metricasClientes' => $this->getMetricasClientes($condicionesFecha),
                'margenPorCategoria' => $this->getMargenPorCategoria(),
                'productosMejorRendimiento' => $this->getProductosMejorRendimiento($condicionesFecha),
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException('Error al cargar estadísticas: ' . $e->getMessage());
        }
    }

    private function getGananciasBrutas(array $condicionesFecha): float
    {
        $sql = "SELECT COALESCE(SUM(total), 0) as total FROM venta
            WHERE estado = 'completada' {$condicionesFecha['venta']}";
        $params = $condicionesFecha['params'] ?? [];
        return (float) $this->entityManager->getConnection()
            ->executeQuery($sql, $params)
            ->fetchOne();
    }

    private function getGastosBrutos(array $condicionesFecha): float
    {
        $sql = "SELECT COALESCE(SUM(total), 0) as total FROM compra WHERE estado = 'completada' {$condicionesFecha['compra']}";
        return (float) $this->entityManager->getConnection()->executeQuery($sql)->fetchOne();
    }

    private function getDineroPendiente(array $condicionesFecha): float
    {
        $sql = "SELECT COALESCE(SUM(total), 0) as total FROM venta WHERE estado = 'pendiente' {$condicionesFecha['venta']}";
        return (float) $this->entityManager->getConnection()->executeQuery($sql)->fetchOne();
    }

    private function getDineroActual(array $condicionesFecha): float
    {
        return $this->getGananciasBrutas($condicionesFecha) - $this->getGastosBrutos($condicionesFecha);
    }

    private function getValorInventario(): float
    {
        $sql = "
            SELECT COALESCE(SUM(p.precio_compra * GREATEST(0,
                COALESCE(dc.total_comprado, 0) - COALESCE(dv.total_vendido, 0) + COALESCE(ai.neto_ajustado, 0)
            )), 0) as valor
            FROM producto p
            LEFT JOIN (
                SELECT producto_id, SUM(cantidad) as total_comprado
                FROM detalle_compra dc
                JOIN compra c ON dc.compra_id = c.id
                WHERE c.estado = 'completada'
                GROUP BY producto_id
            ) dc ON p.id = dc.producto_id
            LEFT JOIN (
                SELECT producto_id, SUM(cantidad) as total_vendido
                FROM detalle_venta dv
                JOIN venta v ON dv.venta_id = v.id
                WHERE v.estado = 'completada'
                GROUP BY producto_id
            ) dv ON p.id = dv.producto_id
            LEFT JOIN (
                SELECT producto_id,
                       SUM(CASE WHEN tipo = 'entrada' THEN cantidad ELSE -cantidad END) as neto_ajustado
                FROM ajuste_inventario
                GROUP BY producto_id
            ) ai ON p.id = ai.producto_id
            WHERE p.activo = true
        ";

        return (float) $this->entityManager->getConnection()->executeQuery($sql)->fetchOne();
    }

    private function getMargenBrutoPromedio(): float
    {
        $sql = "
            SELECT ROUND(COALESCE(AVG(
                CASE
                    WHEN precio_compra > 0 AND precio_venta_actual > precio_compra
                    THEN ((precio_venta_actual - precio_compra) / precio_venta_actual * 100)
                    ELSE NULL
                END
            ), 0), 2) as margen_promedio
            FROM producto
            WHERE activo = true AND precio_venta_actual > 0
        ";

        return (float) $this->entityManager->getConnection()->executeQuery($sql)->fetchOne();
    }

    // Continuación de src/Service/StatisticsService.php

    private function getTicketPromedio(array $condicionesFecha): float
    {
        $sql = "
        SELECT COALESCE(AVG(total), 0) as ticket
        FROM venta
        WHERE estado = 'completada' AND total > 0 {$condicionesFecha['venta']}
    ";

        return (float) $this->entityManager->getConnection()->executeQuery($sql)->fetchOne();
    }

    private function getProductosAgotados(): int
    {
        $stocks = $this->getStocksProductos();

        $agotados = 0;
        foreach ($stocks as $stock) {
            if ($stock['stock_actual'] <= 0) {
                $agotados++;
            }
        }

        return $agotados;
    }

    private function getProductosStockBajo(): int
    {
        $stocks = $this->getStocksProductos();

        $stockBajo = 0;
        foreach ($stocks as $stock) {
            if ($stock['stock_actual'] > 0 && $stock['stock_actual'] <= $stock['stock_minimo']) {
                $stockBajo++;
            }
        }

        return $stockBajo;
    }

    private function getStocksProductos(): array
    {
        $sql = "
        SELECT
            p.id,
            p.nombre,
            p.stock_minimo,
            GREATEST(0,
                COALESCE(dc.total_comprado, 0) -
                COALESCE(dv.total_vendido, 0) +
                COALESCE(ai.neto_ajustado, 0)
            ) as stock_actual
        FROM producto p
        LEFT JOIN (
            SELECT producto_id, SUM(cantidad) as total_comprado
            FROM detalle_compra dc
            JOIN compra c ON dc.compra_id = c.id
            WHERE c.estado = 'completada'
            GROUP BY producto_id
        ) dc ON p.id = dc.producto_id
        LEFT JOIN (
            SELECT producto_id, SUM(cantidad) as total_vendido
            FROM detalle_venta dv
            JOIN venta v ON dv.venta_id = v.id
            WHERE v.estado = 'completada'
            GROUP BY producto_id
        ) dv ON p.id = dv.producto_id
        LEFT JOIN (
            SELECT producto_id,
                   SUM(CASE WHEN tipo = 'entrada' THEN cantidad ELSE -cantidad END) as neto_ajustado
            FROM ajuste_inventario
            GROUP BY producto_id
        ) ai ON p.id = ai.producto_id
        WHERE p.activo = true
    ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();
    }

    private function getTopProductosRentables(): array
    {
        $sql = "
        SELECT
            p.nombre,
            p.precio_venta_actual,
            p.precio_compra,
            (p.precio_venta_actual - p.precio_compra) as margen_absoluto,
            ROUND(
                CASE
                    WHEN p.precio_compra > 0 AND p.precio_venta_actual > p.precio_compra
                    THEN ((p.precio_venta_actual - p.precio_compra) / p.precio_compra * 100)
                    ELSE 0
                END, 2
            ) as margen_porcentual
        FROM producto p
        WHERE p.activo = true
          AND p.precio_venta_actual > 0
          AND p.precio_compra > 0
          AND p.precio_venta_actual > p.precio_compra
        ORDER BY margen_porcentual DESC
        LIMIT 10
    ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();
    }

    private function getTopProveedores(array $condicionesFecha): array
    {
        $sql = "
        SELECT
            pr.nombre,
            COALESCE(SUM(c.total), 0) as total_comprado,
            COUNT(c.id) as total_compras
        FROM proveedor pr
        LEFT JOIN compra c ON pr.id = c.proveedor_id AND c.estado = 'completada' {$condicionesFecha['compra_join']}
        GROUP BY pr.id, pr.nombre
        HAVING total_comprado > 0
        ORDER BY total_comprado DESC
        LIMIT 10
    ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();
    }

    private function getVentasDiarias(array $condicionesFecha): array
    {
        $sql = "
        SELECT
            DATE(v.fecha) as dia,
            COALESCE(SUM(v.total), 0) as total
        FROM venta v
        WHERE v.estado = 'completada' {$condicionesFecha['venta']}
        GROUP BY DATE(v.fecha)
        ORDER BY dia ASC
    ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();
    }

    private function getComprasDiarias(array $condicionesFecha): array
    {
        $sql = "
        SELECT
            DATE(c.fecha) as dia,
            COALESCE(SUM(c.total), 0) as total
        FROM compra c
        WHERE c.estado = 'completada' {$condicionesFecha['compra']}
        GROUP BY DATE(c.fecha)
        ORDER BY dia ASC
    ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();
    }

    private function getMetricasAdicionales(array $condicionesFecha): array
    {
        $sql = "
        SELECT
            (SELECT COUNT(*) FROM producto WHERE activo = true) as total_productos,
            (SELECT COUNT(*) FROM venta WHERE estado = 'completada' {$condicionesFecha['venta']}) as total_ventas,
            (SELECT COUNT(*) FROM compra WHERE estado = 'completada' {$condicionesFecha['compra']}) as total_compras,
            (SELECT COUNT(*) FROM cliente) as total_clientes
    ";

        $metricas = $this->entityManager->getConnection()->executeQuery($sql)->fetchAssociative();

        // Agregar información de diagnóstico del margen
        $infoMargen = $this->getInfoDiagnosticoMargen();
        $metricas['productos_analizados_margen'] = $infoMargen['total_productos'];
        $metricas['productos_problema_margen'] = $infoMargen['productos_problema'];

        // Agregar métricas adicionales
        $metricas['eficiencia_inventario'] = $this->getEficienciaInventario();
        $metricas['tendencias_precios'] = $this->getTendenciasPrecios($condicionesFecha);
        $metricas['rotacion_productos'] = $this->getRotacionProductos($condicionesFecha);
        $metricas['metricas_clientes'] = $this->getMetricasClientes($condicionesFecha);

        return $metricas;
    }

    private function getInfoDiagnosticoMargen(): array
    {
        $sql = "
        SELECT
            COUNT(*) as total_productos,
            SUM(CASE WHEN precio_compra <= 0 OR precio_venta_actual <= precio_compra THEN 1 ELSE 0 END) as productos_problema
        FROM producto
        WHERE activo = true AND precio_venta_actual > 0
    ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAssociative();
    }

    private function getVentasPorCategoria(array $condicionesFecha): array
    {
        $sql = "
        SELECT
            c.nombre as categoria,
            COUNT(DISTINCT v.id) as total_ventas,
            COALESCE(SUM(v.total), 0) as total_ingresos,
            COUNT(DISTINCT dv.producto_id) as productos_vendidos
        FROM categoria c
        LEFT JOIN producto p ON c.id = p.categoria_id
        LEFT JOIN detalle_venta dv ON p.id = dv.producto_id
        LEFT JOIN venta v ON dv.venta_id = v.id AND v.estado = 'completada' {$condicionesFecha['venta_join']}
        GROUP BY c.id, c.nombre
        HAVING total_ingresos > 0
        ORDER BY total_ingresos DESC
        LIMIT 8
    ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();
    }

    private function getEficienciaInventario(): array
    {
        $sql = "
        SELECT
            COUNT(*) as total_productos,
            SUM(CASE WHEN stock_actual <= 0 THEN 1 ELSE 0 END) as agotados,
            SUM(CASE WHEN stock_actual > 0 AND stock_actual <= p.stock_minimo THEN 1 ELSE 0 END) as stock_bajo,
            SUM(CASE WHEN stock_actual > p.stock_minimo THEN 1 ELSE 0 END) as stock_optimo,
            ROUND((SUM(CASE WHEN stock_actual > p.stock_minimo THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as porcentaje_optimo
        FROM (
            SELECT
                p.id,
                p.stock_minimo,
                GREATEST(0,
                    COALESCE(dc.total_comprado, 0) -
                    COALESCE(dv.total_vendido, 0) +
                    COALESCE(ai.neto_ajustado, 0)
                ) as stock_actual
            FROM producto p
            LEFT JOIN (
                SELECT producto_id, SUM(cantidad) as total_comprado
                FROM detalle_compra dc
                JOIN compra c ON dc.compra_id = c.id
                WHERE c.estado = 'completada'
                GROUP BY producto_id
            ) dc ON p.id = dc.producto_id
            LEFT JOIN (
                SELECT producto_id, SUM(cantidad) as total_vendido
                FROM detalle_venta dv
                JOIN venta v ON dv.venta_id = v.id
                WHERE v.estado = 'completada'
                GROUP BY producto_id
            ) dv ON p.id = dv.producto_id
            LEFT JOIN (
                SELECT producto_id,
                       SUM(CASE WHEN tipo = 'entrada' THEN cantidad ELSE -cantidad END) as neto_ajustado
                FROM ajuste_inventario
                GROUP BY producto_id
            ) ai ON p.id = ai.producto_id
            WHERE p.activo = true
        ) stock_calc
        JOIN producto p ON stock_calc.id = p.id
    ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAssociative();
    }

    private function getTendenciasPrecios(array $condicionesFecha): array
    {
        $sql = "
        SELECT
            COUNT(*) as total_cambios,
            AVG(CASE WHEN tipo = 'venta' THEN (precio_nuevo - precio_anterior) / precio_anterior * 100 ELSE NULL END) as avg_incremento_venta,
            AVG(CASE WHEN tipo = 'compra' THEN (precio_nuevo - precio_anterior) / precio_anterior * 100 ELSE NULL END) as avg_incremento_compra,
            MAX(fecha_cambio) as ultimo_cambio
        FROM historial_precios
        WHERE 1=1 {$condicionesFecha['historial_precios']}
    ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAssociative();
    }

    private function getRotacionProductos(array $condicionesFecha): array
    {
        $sql = "
        SELECT
            COUNT(*) as productos_activos,
            SUM(CASE WHEN dv.total_vendido > 0 THEN 1 ELSE 0 END) as productos_vendidos,
            ROUND((SUM(CASE WHEN dv.total_vendido > 0 THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as tasa_rotacion,
            AVG(CASE WHEN dv.total_vendido > 0 THEN dv.total_vendido ELSE 0 END) as ventas_promedio_por_producto
        FROM producto p
        LEFT JOIN (
            SELECT producto_id, SUM(cantidad) as total_vendido
            FROM detalle_venta dv
            JOIN venta v ON dv.venta_id = v.id
            WHERE v.estado = 'completada' {$condicionesFecha['venta_subquery']}
            GROUP BY producto_id
        ) dv ON p.id = dv.producto_id
        WHERE p.activo = true
    ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAssociative();
    }

    private function getMetricasClientes(array $condicionesFecha): array
    {
        $sql = "
        SELECT
            COUNT(DISTINCT c.id) as total_clientes,
            COUNT(DISTINCT CASE WHEN ventas_por_cliente.total_ventas > 1 THEN c.id END) as clientes_recurrentes,
            ROUND((COUNT(DISTINCT CASE WHEN ventas_por_cliente.total_ventas > 1 THEN c.id END) * 100.0 / COUNT(DISTINCT c.id)), 2) as tasa_recurrencia,
            AVG(ventas_por_cliente.total_ventas) as promedio_ventas_por_cliente
        FROM cliente c
        LEFT JOIN (
            SELECT cliente_id, COUNT(*) as total_ventas
            FROM venta
            WHERE estado = 'completada' {$condicionesFecha['venta']}
            GROUP BY cliente_id
        ) ventas_por_cliente ON c.id = ventas_por_cliente.cliente_id
    ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAssociative();
    }

    private function getMargenPorCategoria(): array
    {
        $sql = "
        SELECT
            c.nombre as categoria,
            COUNT(p.id) as total_productos,
            ROUND(AVG(CASE
                WHEN p.precio_compra > 0 AND p.precio_venta_actual > p.precio_compra
                THEN ((p.precio_venta_actual - p.precio_compra) / p.precio_venta_actual * 100)
                ELSE 0
            END), 2) as margen_promedio,
            SUM(CASE WHEN p.precio_compra <= 0 OR p.precio_venta_actual <= p.precio_compra THEN 1 ELSE 0 END) as productos_problema
        FROM categoria c
        LEFT JOIN producto p ON c.id = p.categoria_id AND p.activo = true
        GROUP BY c.id, c.nombre
        HAVING total_productos > 0
        ORDER BY margen_promedio DESC
    ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();
    }

    private function getProductosMejorRendimiento(array $condicionesFecha): array
    {
        $sql = "
        SELECT
            p.nombre,
            p.precio_venta_actual,
            p.precio_compra,
            COALESCE(dv.total_vendido, 0) as unidades_vendidas,
            COALESCE(dv.total_vendido * p.precio_venta_actual, 0) as ingresos_totales,
            ROUND(
                CASE
                    WHEN p.precio_compra > 0 AND p.precio_venta_actual > p.precio_compra
                    THEN ((p.precio_venta_actual - p.precio_compra) / p.precio_compra * 100)
                    ELSE 0
                END, 2
            ) as margen_porcentual,
            (COALESCE(dv.total_vendido, 0) * (p.precio_venta_actual - p.precio_compra)) as ganancia_total
        FROM producto p
        LEFT JOIN (
            SELECT producto_id, SUM(cantidad) as total_vendido
            FROM detalle_venta dv
            JOIN venta v ON dv.venta_id = v.id
            WHERE v.estado = 'completada' {$condicionesFecha['venta_subquery']}
            GROUP BY producto_id
        ) dv ON p.id = dv.producto_id
        WHERE p.activo = true AND p.precio_venta_actual > 0
        ORDER BY ganancia_total DESC
        LIMIT 10
    ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();
    }


    private function getTopClientes(array $condicionesFecha): array
    {
        $sql = "
            SELECT c.nombre, COALESCE(SUM(v.total), 0) as total_gastado, COUNT(v.id) as total_compras
            FROM cliente c
            LEFT JOIN venta v ON c.id = v.cliente_id AND v.estado = 'completada' {$condicionesFecha['venta_join']}
            GROUP BY c.id, c.nombre
            HAVING total_gastado > 0
            ORDER BY total_gastado DESC
            LIMIT 10
        ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();
    }



    private function construirCondicionesFecha(string $filtro, string $fechaEspecifica): array
    {
        $condiciones = [
            'venta' => '',
            'compra' => '',
            'venta_join' => '',
            'compra_join' => '',
            'venta_subquery' => '',
            'historial_precios' => ''
        ];

        // Si el filtro es "todo", no aplicamos condiciones
        if ($filtro === 'todo') {
            return $condiciones;
        }

        // Construir las fechas según el filtro
        $fechaInicio = null;
        $fechaFin = null;

        switch ($filtro) {
            case 'hoy':
                $fechaInicio = (new \DateTime())->format('Y-m-d 00:00:00');
                $fechaFin = (new \DateTime())->format('Y-m-d 23:59:59');
                break;

            case 'ayer':
                $fechaInicio = (new \DateTime('-1 day'))->format('Y-m-d 00:00:00');
                $fechaFin = (new \DateTime('-1 day'))->format('Y-m-d 23:59:59');
                break;

            case 'fecha_especifica':
                if ($fechaEspecifica) {
                    $fechaInicio = (new \DateTime($fechaEspecifica))->format('Y-m-d 00:00:00');
                    $fechaFin = (new \DateTime($fechaEspecifica))->format('Y-m-d 23:59:59');
                }
                break;

            case 'semana_actual':
                $fechaInicio = (new \DateTime('monday this week'))->format('Y-m-d 00:00:00');
                $fechaFin = (new \DateTime())->format('Y-m-d 23:59:59');
                break;

            case 'semana_pasada':
                $fechaInicio = (new \DateTime('monday last week'))->format('Y-m-d 00:00:00');
                $fechaFin = (new \DateTime('sunday last week'))->format('Y-m-d 23:59:59');
                break;

            case 'mes_actual':
                $fechaInicio = (new \DateTime('first day of this month'))->format('Y-m-d 00:00:00');
                $fechaFin = (new \DateTime())->format('Y-m-d 23:59:59');
                break;

            case 'mes_pasado':
                $fechaInicio = (new \DateTime('first day of last month'))->format('Y-m-d 00:00:00');
                $fechaFin = (new \DateTime('last day of last month'))->format('Y-m-d 23:59:59');
                break;

            case 'ultimos_3_meses':
                $fechaInicio = (new \DateTime('-3 months'))->format('Y-m-d 00:00:00');
                $fechaFin = (new \DateTime())->format('Y-m-d 23:59:59');
                break;

            case 'ultimos_6_meses':
                $fechaInicio = (new \DateTime('-6 months'))->format('Y-m-d 00:00:00');
                $fechaFin = (new \DateTime())->format('Y-m-d 23:59:59');
                break;

            case 'ano_actual':
                $year = date('Y');
                $fechaInicio = $year . '-01-01 00:00:00';
                $fechaFin = $year . '-12-31 23:59:59';
                break;

            case 'ano_pasado':
                $year = date('Y') - 1;
                $fechaInicio = $year . '-01-01 00:00:00';
                $fechaFin = $year . '-12-31 23:59:59';
                break;

            default:
                return $condiciones;
        }

        // Construir las condiciones para cada tipo
        if ($fechaInicio && $fechaFin) {
            $condiciones['venta'] = " AND fecha >= '{$fechaInicio}' AND fecha <= '{$fechaFin}'";
            $condiciones['compra'] = " AND fecha >= '{$fechaInicio}' AND fecha <= '{$fechaFin}'";
            $condiciones['venta_join'] = " AND v.fecha >= '{$fechaInicio}' AND v.fecha <= '{$fechaFin}'";
            $condiciones['compra_join'] = " AND c.fecha >= '{$fechaInicio}' AND c.fecha <= '{$fechaFin}'";
            $condiciones['venta_subquery'] = " AND v.fecha >= '{$fechaInicio}' AND v.fecha <= '{$fechaFin}'";
            $condiciones['historial_precios'] = " AND fecha_cambio >= '{$fechaInicio}' AND fecha_cambio <= '{$fechaFin}'";
        }

        return $condiciones;
    }
}
