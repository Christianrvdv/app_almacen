<?php

namespace App\Controller;

use App\Entity\Producto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Venta;
use App\Entity\Compra;
use Doctrine\ORM\EntityManagerInterface;

class EstadisticasController extends AbstractController
{
    #[Route('/estadisticas', name: 'app_estadisticas_index')]
    public function index(EntityManagerInterface $entityManager)
    {
        // calcular ganancias totales
        $ventas = $entityManager->getRepository(Venta::class)->findAll();
        $gananciasBrutas = 0;
        $dineroPendiente = 0;
        foreach ($ventas as $venta) {
            $gananciasBrutas += $venta->getTotal();
            if ($venta->getEstado() === 'pendiente') {
                $dineroPendiente += $venta->getTotal();
            }
        }

        // calcular gastos totales
        $compras = $entityManager->getRepository(Compra::class)->findAll();
        $gastosBrutos = 0;
        foreach ($compras as $compra) {
            $gastosBrutos += $compra->getTotal();
        }

        // calcular dinero actual
        $dineroActual = $gananciasBrutas - $gastosBrutos;

        // estadisticas controller.php
        $sqlVentas = "SELECT DATE(v.fecha) as dia, SUM(v.total) as total FROM venta v GROUP BY dia";
        $ventasDiarias = $entityManager->getConnection()->executeQuery($sqlVentas)->fetchAllAssociative();

        $sqlCompras = "SELECT DATE(c.fecha) as dia, SUM(c.total) as total FROM compra c GROUP BY dia";
        $comprasDiarias = $entityManager->getConnection()->executeQuery($sqlCompras)->fetchAllAssociative();

        // calcular la cantidad de productos agotados
        $productosAgotados = $entityManager->getRepository(Producto::class)->count(['stock_minimo' => 0]);


        return $this->render('estadisticas/index.html.twig', [
            'gananciasBrutas' => $gananciasBrutas,
            'gastosBrutos' => $gastosBrutos,
            'dineroActual' => $dineroActual,
            'dineroPendiente' => $dineroPendiente,
            'ventasDiarias' => $ventasDiarias,
            'comprasDiarias' => $comprasDiarias,
            'productosAgotados' => $productosAgotados
        ]);
    }
}
