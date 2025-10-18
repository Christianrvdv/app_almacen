<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EstadisticasController extends AbstractController
{
    #[Route('/estadisticas', name: 'app_estadisticas_index')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $connection = $entityManager->getConnection();

        // Obtener parámetros de filtro
        $filtro = $request->query->get('filtro', 'mes_actual');
        $fechaEspecifica = $request->query->get('fecha_especifica', date('Y-m-d'));

        // Construir condiciones WHERE basadas en el filtro
        $condicionesFecha = $this->construirCondicionesFecha($filtro, $fechaEspecifica);

        try {
            // 1. Ganancias brutas (ventas totales) - con filtro de fecha
            $sqlVentasTotales = "SELECT COALESCE(SUM(total), 0) as total FROM venta WHERE estado = 'completada' {$condicionesFecha['venta']}";
            $gananciasBrutas = $connection->executeQuery($sqlVentasTotales)->fetchOne();

            // 2. Gastos brutos (compras totales) - con filtro de fecha
            $sqlComprasTotales = "SELECT COALESCE(SUM(total), 0) as total FROM compra WHERE estado = 'completada' {$condicionesFecha['compra']}";
            $gastosBrutos = $connection->executeQuery($sqlComprasTotales)->fetchOne();

            // 3. Dinero pendiente (ventas pendientes) - con filtro de fecha
            $sqlVentasPendientes = "SELECT COALESCE(SUM(total), 0) as total FROM venta WHERE estado = 'pendiente' {$condicionesFecha['venta']}";
            $dineroPendiente = $connection->executeQuery($sqlVentasPendientes)->fetchOne();

            // 4. Dinero actual (gananciasBrutas - gastosBrutos)
            $dineroActual = $gananciasBrutas - $gastosBrutos;

            // 5. Valor del inventario (no se filtra por fecha - estado actual)
            $sqlInventario = "
                SELECT
                    COALESCE(SUM(p.precio_compra *
                        GREATEST(0,
                            COALESCE(dc.total_comprado, 0) -
                            COALESCE(dv.total_vendido, 0) +
                            COALESCE(ai.neto_ajustado, 0)
                        )
                    ), 0) as valor
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
            $valorInventario = $connection->executeQuery($sqlInventario)->fetchOne();

            // 6. Margen bruto promedio
            $sqlMargen = "
                SELECT
                    ROUND(
                        COALESCE(
                            AVG(
                                CASE
                                    WHEN precio_compra > 0 AND precio_venta_actual > precio_compra
                                    THEN ((precio_venta_actual - precio_compra) / precio_venta_actual * 100)
                                    ELSE NULL
                                END
                            ), 0
                        ), 2
                    ) as margen_promedio,
                    COUNT(*) as total_productos,
                    SUM(CASE WHEN precio_compra <= 0 OR precio_venta_actual <= precio_compra THEN 1 ELSE 0 END) as productos_problema
                FROM producto
                WHERE activo = true AND precio_venta_actual > 0
            ";
            $resultadoMargen = $connection->executeQuery($sqlMargen)->fetchAssociative();
            $margenBrutoPromedio = $resultadoMargen['margen_promedio'];
            $totalProductosAnalizados = $resultadoMargen['total_productos'];
            $productosConProblema = $resultadoMargen['productos_problema'];

            // 7. Ticket promedio - con filtro de fecha
            $sqlTicket = "
                SELECT COALESCE(AVG(total), 0) as ticket
                FROM venta
                WHERE estado = 'completada' AND total > 0 {$condicionesFecha['venta']}
            ";
            $ticketPromedio = $connection->executeQuery($sqlTicket)->fetchOne();

            // 8. Productos agotados y stock bajo
            $sqlStock = "
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
            $stocks = $connection->executeQuery($sqlStock)->fetchAllAssociative();

            $productosAgotados = 0;
            $productosStockBajo = 0;
            foreach ($stocks as $stock) {
                if ($stock['stock_actual'] <= 0) {
                    $productosAgotados++;
                } elseif ($stock['stock_actual'] <= $stock['stock_minimo']) {
                    $productosStockBajo++;
                }
            }

            // 9. Top 10 productos más rentables
            $sqlProductosRentables = "
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
                WHERE p.activo = true AND p.precio_venta_actual > 0 AND p.precio_compra > 0 AND p.precio_venta_actual > p.precio_compra
                ORDER BY margen_porcentual DESC
                LIMIT 10
            ";
            $topProductosRentables = $connection->executeQuery($sqlProductosRentables)->fetchAllAssociative();

            // 10. Top 10 clientes (por total gastado) - con filtro de fecha
            $sqlClientes = "
                SELECT
                    c.nombre,
                    COALESCE(SUM(v.total), 0) as total_gastado,
                    COUNT(v.id) as total_compras
                FROM cliente c
                LEFT JOIN venta v ON c.id = v.cliente_id AND v.estado = 'completada' {$condicionesFecha['venta_join']}
                GROUP BY c.id, c.nombre
                HAVING total_gastado > 0
                ORDER BY total_gastado DESC
                LIMIT 10
            ";
            $topClientes = $connection->executeQuery($sqlClientes)->fetchAllAssociative();

            // 11. Top 10 proveedores (por total comprado) - con filtro de fecha
            $sqlProveedores = "
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
            $topProveedores = $connection->executeQuery($sqlProveedores)->fetchAllAssociative();

            // 12. Ventas y compras diarias (para el gráfico) - con filtro de fecha
            $sqlVentasDiarias = "
                SELECT
                    DATE(v.fecha) as dia,
                    COALESCE(SUM(v.total), 0) as total
                FROM venta v
                WHERE v.estado = 'completada' {$condicionesFecha['venta']}
                GROUP BY DATE(v.fecha)
                ORDER BY dia ASC
            ";
            $ventasDiarias = $connection->executeQuery($sqlVentasDiarias)->fetchAllAssociative();

            $sqlComprasDiarias = "
                SELECT
                    DATE(c.fecha) as dia,
                    COALESCE(SUM(c.total), 0) as total
                FROM compra c
                WHERE c.estado = 'completada' {$condicionesFecha['compra']}
                GROUP BY DATE(c.fecha)
                ORDER BY dia ASC
            ";
            $comprasDiarias = $connection->executeQuery($sqlComprasDiarias)->fetchAllAssociative();

            // 13. Métricas adicionales para insights
            $sqlMetricasAdicionales = "
                SELECT
                    (SELECT COUNT(*) FROM producto WHERE activo = true) as total_productos,
                    (SELECT COUNT(*) FROM venta WHERE estado = 'completada' {$condicionesFecha['venta']}) as total_ventas,
                    (SELECT COUNT(*) FROM compra WHERE estado = 'completada' {$condicionesFecha['compra']}) as total_compras,
                    (SELECT COUNT(*) FROM cliente) as total_clientes
            ";
            $metricasAdicionales = $connection->executeQuery($sqlMetricasAdicionales)->fetchAssociative();

            // Agregar información de diagnóstico del margen
            $metricasAdicionales['productos_analizados_margen'] = $totalProductosAnalizados;
            $metricasAdicionales['productos_problema_margen'] = $productosConProblema;

            // 14. Ventas por categoría - con filtro de fecha
            $sqlVentasPorCategoria = "
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
            $ventasPorCategoria = $connection->executeQuery($sqlVentasPorCategoria)->fetchAllAssociative();

            // 15. Eficiencia de inventario (no se filtra por fecha)
            $sqlEficienciaInventario = "
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
            $eficienciaInventario = $connection->executeQuery($sqlEficienciaInventario)->fetchAssociative();

            // 16. Tendencias de precios - con filtro de fecha
            $sqlTendenciasPrecios = "
                SELECT
                    COUNT(*) as total_cambios,
                    AVG(CASE WHEN tipo = 'venta' THEN (precio_nuevo - precio_anterior) / precio_anterior * 100 ELSE NULL END) as avg_incremento_venta,
                    AVG(CASE WHEN tipo = 'compra' THEN (precio_nuevo - precio_anterior) / precio_anterior * 100 ELSE NULL END) as avg_incremento_compra,
                    MAX(fecha_cambio) as ultimo_cambio
                FROM historial_precios
                WHERE 1=1 {$condicionesFecha['historial_precios']}
            ";
            $tendenciasPrecios = $connection->executeQuery($sqlTendenciasPrecios)->fetchAssociative();

            // 17. Rotación de productos - con filtro de fecha
            $sqlRotacionProductos = "
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
            $rotacionProductos = $connection->executeQuery($sqlRotacionProductos)->fetchAssociative();

            // 18. Métricas de clientes recurrentes - con filtro de fecha
            $sqlClientesRecurrentes = "
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
            $metricasClientes = $connection->executeQuery($sqlClientesRecurrentes)->fetchAssociative();

            // 19. Análisis de margen por categoría (no se filtra por fecha)
            $sqlMargenCategoria = "
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
            $margenPorCategoria = $connection->executeQuery($sqlMargenCategoria)->fetchAllAssociative();

            // 20. Productos con mejor rendimiento (combinando margen y ventas) - con filtro de fecha
            $sqlProductosRendimiento = "
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
            $productosMejorRendimiento = $connection->executeQuery($sqlProductosRendimiento)->fetchAllAssociative();

            // Agregar al array de metricasAdicionales
            $metricasAdicionales['eficiencia_inventario'] = $eficienciaInventario;
            $metricasAdicionales['tendencias_precios'] = $tendenciasPrecios;
            $metricasAdicionales['rotacion_productos'] = $rotacionProductos;
            $metricasAdicionales['metricas_clientes'] = $metricasClientes;

        } catch (\Exception $e) {
            // En caso de error, establecer valores por defecto
            $gananciasBrutas = 0;
            $gastosBrutos = 0;
            $dineroPendiente = 0;
            $dineroActual = 0;
            $valorInventario = 0;
            $margenBrutoPromedio = 0;
            $ticketPromedio = 0;
            $productosAgotados = 0;
            $productosStockBajo = 0;
            $topProductosRentables = [];
            $topClientes = [];
            $topProveedores = [];
            $ventasDiarias = [];
            $comprasDiarias = [];
            $metricasAdicionales = [
                'total_productos' => 0,
                'total_ventas' => 0,
                'total_compras' => 0,
                'total_clientes' => 0,
                'productos_analizados_margen' => 0,
                'productos_problema_margen' => 0,
                'eficiencia_inventario' => [
                    'total_productos' => 0,
                    'agotados' => 0,
                    'stock_bajo' => 0,
                    'stock_optimo' => 0,
                    'porcentaje_optimo' => 0
                ],
                'tendencias_precios' => [
                    'total_cambios' => 0,
                    'avg_incremento_venta' => 0,
                    'avg_incremento_compra' => 0
                ],
                'rotacion_productos' => [
                    'productos_activos' => 0,
                    'productos_vendidos' => 0,
                    'tasa_rotacion' => 0,
                    'ventas_promedio_por_producto' => 0
                ],
                'metricas_clientes' => [
                    'total_clientes' => 0,
                    'clientes_recurrentes' => 0,
                    'tasa_recurrencia' => 0,
                    'promedio_ventas_por_cliente' => 0
                ]
            ];
            $ventasPorCategoria = [];
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
                'avg_incremento_compra' => 0
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
            $margenPorCategoria = [];
            $productosMejorRendimiento = [];

            $this->addFlash('error', 'Error al cargar las estadísticas: ' . $e->getMessage());
        }

        return $this->render('estadisticas/index.html.twig', [
            'gananciasBrutas' => (float)$gananciasBrutas,
            'gastosBrutos' => (float)$gastosBrutos,
            'dineroActual' => (float)$dineroActual,
            'dineroPendiente' => (float)$dineroPendiente,
            'valorInventario' => (float)$valorInventario,
            'margenBrutoPromedio' => (float)$margenBrutoPromedio,
            'ticketPromedio' => (float)$ticketPromedio,
            'productosAgotados' => $productosAgotados,
            'productosStockBajo' => $productosStockBajo,
            'topProductosRentables' => $topProductosRentables,
            'topClientes' => $topClientes,
            'topProveedores' => $topProveedores,
            'ventasDiarias' => $ventasDiarias,
            'comprasDiarias' => $comprasDiarias,
            'metricasAdicionales' => $metricasAdicionales,
            'ventasPorCategoria' => $ventasPorCategoria,
            'eficienciaInventario' => $eficienciaInventario,
            'tendenciasPrecios' => $tendenciasPrecios,
            'rotacionProductos' => $rotacionProductos,
            'metricasClientes' => $metricasClientes,
            'margenPorCategoria' => $margenPorCategoria,
            'productosMejorRendimiento' => $productosMejorRendimiento,
            'filtro_actual' => $filtro,
            'fecha_especifica' => $fechaEspecifica,
        ]);
    }

    // ... (otros métodos se mantienen igual)

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
