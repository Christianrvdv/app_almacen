<?php

namespace App\Controller;

use App\Entity\DetalleCompra;
use App\Form\DetalleCompraType;
use App\Repository\DetalleCompraRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/detalle/compra')]
final class DetalleCompraController extends AbstractController
{
    #[Route(name: 'app_detalle_compra_index', methods: ['GET'])]
    public function index(DetalleCompraRepository $detalleCompraRepository): Response
    {
        return $this->render('detalle_compra/index.html.twig', [
            'detalle_compras' => $detalleCompraRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_detalle_compra_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $detalleCompra = new DetalleCompra();
        $form = $this->createForm(DetalleCompraType::class, $detalleCompra);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($detalleCompra);
            $entityManager->flush();

            return $this->redirectToRoute('app_detalle_compra_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('detalle_compra/new.html.twig', [
            'detalle_compra' => $detalleCompra,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_detalle_compra_show', methods: ['GET'])]
    public function show(DetalleCompra $detalleCompra): Response
    {
        return $this->render('detalle_compra/show.html.twig', [
            'detalle_compra' => $detalleCompra,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_detalle_compra_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DetalleCompra $detalleCompra, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DetalleCompraType::class, $detalleCompra);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_detalle_compra_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('detalle_compra/edit.html.twig', [
            'detalle_compra' => $detalleCompra,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_detalle_compra_delete', methods: ['POST'])]
    public function delete(Request $request, DetalleCompra $detalleCompra, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$detalleCompra->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($detalleCompra);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_detalle_compra_index', [], Response::HTTP_SEE_OTHER);
    }
}
