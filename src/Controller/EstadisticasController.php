<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EstadisticasController extends AbstractController
{
    #[Route('/estadisticas', name: 'app_estadisticas_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $connection = $entityManager->getConnection();

        try {
            // 1. Ganancias brutas (ventas totales) - solo ventas completadas
            $sqlVentasTotales = "SELECT COALESCE(SUM(total), 0) as total FROM venta WHERE estado = 'completada'";
            $gananciasBrutas = $connection->executeQuery($sqlVentasTotales)->fetchOne();

            // 2. Gastos brutos (compras totales) - solo compras completadas
            $sqlComprasTotales = "SELECT COALESCE(SUM(total), 0) as total FROM compra WHERE estado = 'completada'";
            $gastosBrutos = $connection->executeQuery($sqlComprasTotales)->fetchOne();

            // 3. Dinero pendiente (ventas pendientes)
            $sqlVentasPendientes = "SELECT COALESCE(SUM(total), 0) as total FROM venta WHERE estado = 'pendiente'";
            $dineroPendiente = $connection->executeQuery($sqlVentasPendientes)->fetchOne();

            // 4. Dinero actual (gananciasBrutas - gastosBrutos)
            $dineroActual = $gananciasBrutas - $gastosBrutos;

            // 5. Valor del inventario - CORREGIDO: campo activo como boolean
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

            // 6. Margen bruto promedio - MEJORADO: excluye productos con problemas de precio
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

            // 7. Ticket promedio
            $sqlTicket = "
                SELECT COALESCE(AVG(total), 0) as ticket
                FROM venta
                WHERE estado = 'completada' AND total > 0
            ";
            $ticketPromedio = $connection->executeQuery($sqlTicket)->fetchOne();

            // 8. Productos agotados y stock bajo - CORREGIDO: campo activo como boolean
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

            // 9. Top 10 productos más rentables - MEJORADO: solo productos con margen válido
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

            // 10. Top 10 clientes (por total gastado)
            $sqlClientes = "
                SELECT
                    c.nombre,
                    COALESCE(SUM(v.total), 0) as total_gastado,
                    COUNT(v.id) as total_compras
                FROM cliente c
                LEFT JOIN venta v ON c.id = v.cliente_id AND v.estado = 'completada'
                GROUP BY c.id, c.nombre
                HAVING total_gastado > 0
                ORDER BY total_gastado DESC
                LIMIT 10
            ";
            $topClientes = $connection->executeQuery($sqlClientes)->fetchAllAssociative();

            // 11. Top 10 proveedores (por total comprado)
            $sqlProveedores = "
                SELECT
                    pr.nombre,
                    COALESCE(SUM(c.total), 0) as total_comprado,
                    COUNT(c.id) as total_compras
                FROM proveedor pr
                LEFT JOIN compra c ON pr.id = c.proveedor_id AND c.estado = 'completada'
                GROUP BY pr.id, pr.nombre
                HAVING total_comprado > 0
                ORDER BY total_comprado DESC
                LIMIT 10
            ";
            $topProveedores = $connection->executeQuery($sqlProveedores)->fetchAllAssociative();

            // 12. Ventas y compras diarias (para el gráfico) - últimos 30 días
            $sqlVentasDiarias = "
                SELECT
                    DATE(v.fecha) as dia,
                    COALESCE(SUM(v.total), 0) as total
                FROM venta v
                WHERE v.estado = 'completada'
                AND v.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY DATE(v.fecha)
                ORDER BY dia ASC
            ";
            $ventasDiarias = $connection->executeQuery($sqlVentasDiarias)->fetchAllAssociative();

            $sqlComprasDiarias = "
                SELECT
                    DATE(c.fecha) as dia,
                    COALESCE(SUM(c.total), 0) as total
                FROM compra c
                WHERE c.estado = 'completada'
                AND c.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY DATE(c.fecha)
                ORDER BY dia ASC
            ";
            $comprasDiarias = $connection->executeQuery($sqlComprasDiarias)->fetchAllAssociative();

            // 13. Métricas adicionales para insights - CORREGIDO: campo activo como boolean
            $sqlMetricasAdicionales = "
                SELECT
                    (SELECT COUNT(*) FROM producto WHERE activo = true) as total_productos,
                    (SELECT COUNT(*) FROM venta WHERE estado = 'completada') as total_ventas,
                    (SELECT COUNT(*) FROM compra WHERE estado = 'completada') as total_compras,
                    (SELECT COUNT(*) FROM cliente) as total_clientes
            ";
            $metricasAdicionales = $connection->executeQuery($sqlMetricasAdicionales)->fetchAssociative();

            // Agregar información de diagnóstico del margen
            $metricasAdicionales['productos_analizados_margen'] = $totalProductosAnalizados;
            $metricasAdicionales['productos_problema_margen'] = $productosConProblema;

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
                'productos_problema_margen' => 0
            ];

            $this->addFlash('error', 'Error al cargar las estadísticas: ' . $e->getMessage());
        }

        return $this->render('estadisticas/index.html.twig', [
            'gananciasBrutas' => (float) $gananciasBrutas,
            'gastosBrutos' => (float) $gastosBrutos,
            'dineroActual' => (float) $dineroActual,
            'dineroPendiente' => (float) $dineroPendiente,
            'valorInventario' => (float) $valorInventario,
            'margenBrutoPromedio' => (float) $margenBrutoPromedio,
            'ticketPromedio' => (float) $ticketPromedio,
            'productosAgotados' => $productosAgotados,
            'productosStockBajo' => $productosStockBajo,
            'topProductosRentables' => $topProductosRentables,
            'topClientes' => $topClientes,
            'topProveedores' => $topProveedores,
            'ventasDiarias' => $ventasDiarias,
            'comprasDiarias' => $comprasDiarias,
            'metricasAdicionales' => $metricasAdicionales,
        ]);
    }

    #[Route('/estadisticas/exportar', name: 'app_estadisticas_exportar')]
    public function exportar(EntityManagerInterface $entityManager): Response
    {
        $this->addFlash('info', 'Función de exportación en desarrollo');
        return $this->redirectToRoute('app_estadisticas_index');
    }

    #[Route('/estadisticas/actualizar', name: 'app_estadisticas_actualizar')]
    public function actualizar(): Response
    {
        $this->addFlash('success', 'Estadísticas actualizadas correctamente');
        return $this->redirectToRoute('app_estadisticas_index');
    }

    #[Route('/estadisticas/diagnostico-margen', name: 'app_estadisticas_diagnostico_margen')]
    public function diagnosticoMargen(EntityManagerInterface $entityManager): Response
    {
        $connection = $entityManager->getConnection();

        $sqlDiagnostico = "
            SELECT
                nombre,
                precio_compra,
                precio_venta_actual,
                ROUND(((precio_venta_actual - precio_compra) / precio_venta_actual * 100), 2) as margen_porcentual,
                CASE
                    WHEN precio_compra <= 0 THEN 'PRECIO_COMPRA_INVALIDO'
                    WHEN precio_venta_actual <= precio_compra THEN 'SIN_MARGEN'
                    WHEN ((precio_venta_actual - precio_compra) / precio_venta_actual * 100) < 20 THEN 'MARGEN_BAJO'
                    WHEN ((precio_venta_actual - precio_compra) / precio_venta_actual * 100) BETWEEN 20 AND 40 THEN 'MARGEN_MEDIO'
                    ELSE 'MARGEN_ALTO'
                END as categoria_margen
            FROM producto
            WHERE activo = true AND precio_venta_actual > 0
            ORDER BY margen_porcentual ASC;
        ";

        $diagnostico = $connection->executeQuery($sqlDiagnostico)->fetchAllAssociative();

        return $this->render('estadisticas/diagnostico_margen.html.twig', [
            'diagnostico' => $diagnostico,
        ]);
    }
}
