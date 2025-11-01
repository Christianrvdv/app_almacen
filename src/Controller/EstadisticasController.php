<?php

namespace App\Controller;

use App\Service\StatisticsService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EstadisticasController extends AbstractController
{
    private array $defaultStats;

    public function __construct()
    {
        // Definir todas las estadísticas por defecto una sola vez
        $this->defaultStats = $this->initializeDefaultStats();
    }

    #[Route('/estadisticas', name: 'app_estadisticas_index')]
    public function index(Request $request, StatisticsService $statisticsService, LoggerInterface $logger): Response
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
            // Log del error para debugging
            $logger->error('Error cargando estadísticas: ' . $e->getMessage(), [
                'filtro' => $filtro,
                'fecha_especifica' => $fechaEspecifica,
                'trace' => $e->getTraceAsString()
            ]);

            $this->addFlash('error', 'Error al cargar las estadísticas. Se muestran datos por defecto.');

            return $this->render('estadisticas/index.html.twig', array_merge(
                $this->defaultStats,
                [
                    'filtro_actual' => $filtro,
                    'fecha_especifica' => $fechaEspecifica,
                ]
            ));
        }
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

    private function getDefaultStats(): array
    {
        return $this->defaultStats;
    }

    private function getDefaultMetricasAdicionales(): array
    {
        return $this->defaultStats['metricasAdicionales'];
    }

    private function getDefaultEficienciaInventario(): array
    {
        return $this->defaultStats['eficienciaInventario'];
    }

    private function getDefaultTendenciasPrecios(): array
    {
        return $this->defaultStats['tendenciasPrecios'];
    }

    private function getDefaultRotacionProductos(): array
    {
        return $this->defaultStats['rotacionProductos'];
    }

    private function getDefaultMetricasClientes(): array
    {
        return $this->defaultStats['metricasClientes'];
    }
}
