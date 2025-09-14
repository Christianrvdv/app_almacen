<?php

namespace App\Controller;

use App\Entity\AjusteInventario;
use App\Form\AjusteInventarioType;
use App\Repository\AjusteInventarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ajuste/inventario')]
final class AjusteInventarioController extends AbstractController
{
    #[Route(name: 'app_ajuste_inventario_index', methods: ['GET'])]
    public function index(AjusteInventarioRepository $ajusteInventarioRepository): Response
    {
        return $this->render('ajuste_inventario/index.html.twig', [
            'ajuste_inventarios' => $ajusteInventarioRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_ajuste_inventario_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ajusteInventario = new AjusteInventario();
        $form = $this->createForm(AjusteInventarioType::class, $ajusteInventario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ajusteInventario);
            $entityManager->flush();

            return $this->redirectToRoute('app_ajuste_inventario_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ajuste_inventario/new.html.twig', [
            'ajuste_inventario' => $ajusteInventario,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ajuste_inventario_show', methods: ['GET'])]
    public function show(AjusteInventario $ajusteInventario): Response
    {
        return $this->render('ajuste_inventario/show.html.twig', [
            'ajuste_inventario' => $ajusteInventario,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ajuste_inventario_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AjusteInventario $ajusteInventario, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AjusteInventarioType::class, $ajusteInventario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ajuste_inventario_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ajuste_inventario/edit.html.twig', [
            'ajuste_inventario' => $ajusteInventario,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ajuste_inventario_delete', methods: ['POST'])]
    public function delete(Request $request, AjusteInventario $ajusteInventario, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ajusteInventario->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ajusteInventario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ajuste_inventario_index', [], Response::HTTP_SEE_OTHER);
    }
}
