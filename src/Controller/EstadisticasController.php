<?php

namespace App\Controller;

use App\Service\StatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EstadisticasController extends AbstractController
{
    #[Route('/estadisticas', name: 'app_estadisticas_index')]
    public function index(Request $request, StatisticsService $statisticsService): Response
    {
        $filtro = $request->query->get('filtro', 'mes_actual');
        $fechaEspecifica = $request->query->get('fecha_especifica', date('Y-m-d'));

        try {
            $stats = $statisticsService->getDashboardStatistics($filtro, $fechaEspecifica);

            return $this->render('estadisticas/index.html.twig', array_merge($stats, [
                'filtro_actual' => $filtro,
                'fecha_especifica' => $fechaEspecifica,
            ]));

        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al cargar las estadísticas: ' . $e->getMessage());

            // Redirigir a la misma página con estadísticas por defecto
            return $this->render('estadisticas/index.html.twig', array_merge(
                $this->getDefaultStats(),
                [
                    'filtro_actual' => $filtro,
                    'fecha_especifica' => $fechaEspecifica,
                ]
            ));
        }
    }

    private function getDefaultStats(): array
    {
        return [
            'gananciasBrutas' => 0,
            'gastosBrutos' => 0,
            'dineroActual' => 0,
            'dineroPendiente' => 0,
            'valorInventario' => 0,
            'margenBrutoPromedio' => 0,
            'ticketPromedio' => 0,
            'productosAgotados' => 0,
            'productosStockBajo' => 0,
            'topProductosRentables' => [],
            'topClientes' => [],
            'topProveedores' => [],
            'ventasDiarias' => [],
            'comprasDiarias' => [],
            'metricasAdicionales' => $this->getDefaultMetricasAdicionales(),
            'ventasPorCategoria' => [],
            'eficienciaInventario' => $this->getDefaultEficienciaInventario(),
            'tendenciasPrecios' => $this->getDefaultTendenciasPrecios(),
            'rotacionProductos' => $this->getDefaultRotacionProductos(),
            'metricasClientes' => $this->getDefaultMetricasClientes(),
            'margenPorCategoria' => [],
            'productosMejorRendimiento' => [],
        ];
    }

    private function getDefaultMetricasAdicionales(): array
    {
        return [
            'total_productos' => 0,
            'total_ventas' => 0,
            'total_compras' => 0,
            'total_clientes' => 0,
            'productos_analizados_margen' => 0,
            'productos_problema_margen' => 0,
            'eficiencia_inventario' => $this->getDefaultEficienciaInventario(),
            'tendencias_precios' => $this->getDefaultTendenciasPrecios(),
            'rotacion_productos' => $this->getDefaultRotacionProductos(),
            'metricas_clientes' => $this->getDefaultMetricasClientes(),
        ];
    }

    private function getDefaultEficienciaInventario(): array
    {
        return [
            'total_productos' => 0,
            'agotados' => 0,
            'stock_bajo' => 0,
            'stock_optimo' => 0,
            'porcentaje_optimo' => 0
        ];
    }

    private function getDefaultTendenciasPrecios(): array
    {
        return [
            'total_cambios' => 0,
            'avg_incremento_venta' => 0,
            'avg_incremento_compra' => 0,
            'ultimo_cambio' => null
        ];
    }

    private function getDefaultRotacionProductos(): array
    {
        return [
            'productos_activos' => 0,
            'productos_vendidos' => 0,
            'tasa_rotacion' => 0,
            'ventas_promedio_por_producto' => 0
        ];
    }

    private function getDefaultMetricasClientes(): array
    {
        return [
            'total_clientes' => 0,
            'clientes_recurrentes' => 0,
            'tasa_recurrencia' => 0,
            'promedio_ventas_por_cliente' => 0
        ];
    }
}
