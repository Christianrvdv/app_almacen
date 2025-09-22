<?php

namespace App\Controller;

use App\Repository\CategoriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/estadisticas')]
final class EstadisticasController extends AbstractController
{
    #[Route(name: 'app_estadisticas_index')]
    public function index(CategoriaRepository $categoriaRepository): Response
    {
        return $this->render('estadisticas/index.html.twig');
    }
}
