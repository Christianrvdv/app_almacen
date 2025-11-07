<?php

namespace App\Controller;

use App\Service\EstadisticasProviderInterface;
use App\Service\DefaultStatisticsProviderInterface;
use App\Service\StatisticsErrorHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EstadisticasController extends AbstractController
{
    public function __construct(
        private EstadisticasProviderInterface $estadisticasProvider,
        private DefaultStatisticsProviderInterface $defaultStatisticsProvider,
        private StatisticsErrorHandlerInterface $errorHandler
    ) {}

    #[Route('/estadisticas', name: 'app_estadisticas_index')]
    public function index(Request $request): Response
    {
        $filtro = $request->query->get('filtro', 'mes_actual');
        $fechaEspecifica = $request->query->get('fecha_especifica', date('Y-m-d'));

        try {
            $stats = $this->estadisticasProvider->getStatistics($filtro, $fechaEspecifica);

            return $this->render('estadisticas/index.html.twig', array_merge($stats, [
                'filtro_actual' => $filtro,
                'fecha_especifica' => $fechaEspecifica,
            ]));

        } catch (\Exception $e) {
            $this->errorHandler->handleError($e, $filtro, $fechaEspecifica);
            $this->addFlash('error', 'Error al cargar las estadísticas. Se muestran datos por defecto.');

            $defaultStats = $this->defaultStatisticsProvider->getDefaultStatistics();

            return $this->render('estadisticas/index.html.twig', array_merge(
                $defaultStats,
                [
                    'filtro_actual' => $filtro,
                    'fecha_especifica' => $fechaEspecifica,
                ]
            ));
        }
    }
}
