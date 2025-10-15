<?php

namespace App\Controller;

use App\Entity\Compra;
use App\Entity\DetalleCompra;
use App\Entity\Producto;
use App\Form\CompraType;
use App\Form\DetalleCompraType;
use App\Repository\CompraRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/compra')]
final class CompraController extends AbstractController
{
    #[Route(name: 'app_compra_index', methods: ['GET'])]
    public function index(CompraRepository $compraRepository): Response
    {
        return $this->render('compra/index.html.twig', [
            'compras' => $compraRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_compra_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fecha_actual = new \DateTime('now',new \DateTimeZone('America/Toronto'));
        $compra = new Compra();
        $compra->setFecha($fecha_actual);
        $form = $this->createForm(CompraType::class, $compra);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($compra->getDetalleCompras() as $detalle) {
                $detalle->setSubtotal(
                    $detalle->getCantidad() * $detalle->getPrecioUnitario());
                $entityManager->persist($detalle);
            }

            $entityManager->persist($compra);
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_compra_show',
                ['id' => $compra->getId()],
                Response::HTTP_SEE_OTHER);
        }

        $detalleCompra = new DetalleCompra();
        $formDetalle = $this->createForm(DetalleCompraType::class, $detalleCompra);

        return $this->render('compra/new.html.twig', [
            'compra' => $compra,
            'form' => $form,
            'formDetalle' => $formDetalle,
        ]);
    }


    #[Route('/new/{id}', name: 'app_compra_new_by_id', methods: ['GET', 'POST'])]
    public function newByID(Request $request, EntityManagerInterface $entityManager, Producto $producto): Response
    {
        $fecha_actual = new \DateTime('now', new \DateTimeZone('America/Toronto'));
        $compra = new Compra();
        $compra->setFecha($fecha_actual);
        $compra->setProveedor($producto->getProveedor());

        // Crear y agregar el detalle a la compra
        $detalleCompra = new DetalleCompra();
        $detalleCompra->setProducto($producto);
        $detalleCompra->setPrecioUnitario($producto->getPrecioCompra());
        $detalleCompra->setCantidad(0);
        $detalleCompra->setSubtotal(0); // También establecer subtotal en 0

        // AGREGAR EL DETALLE A LA COMPRA
        $compra->addDetalleCompra($detalleCompra);

        $form = $this->createForm(CompraType::class, $compra);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($compra->getDetalleCompras() as $detalle) {
                $detalle->setSubtotal(
                    $detalle->getCantidad() * $detalle->getPrecioUnitario());
                $entityManager->persist($detalle);
            }

            $entityManager->persist($compra);
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_compra_show',
                ['id' => $compra->getId()],
                Response::HTTP_SEE_OTHER);
        }

        // El formDetalle separado ya no es necesario si el detalle está en la compra
        return $this->render('compra/new.html.twig', [
            'compra' => $compra,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_compra_show', methods: ['GET'])]
    public function show(Compra $compra): Response
    {
        $detalle_compras = $compra->getDetalleCompras();

        return $this->render('compra/show.html.twig', [
            'compra' => $compra,
            'detalle_compras' => $detalle_compras,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_compra_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Compra $compra, EntityManagerInterface $entityManager): Response
    {
        // Guardar detalles originales antes del handleRequest
        $originalDetalles = new ArrayCollection();
        foreach ($compra->getDetalleCompras() as $detalle) {
            $originalDetalles->add($detalle);
        }

        $form = $this->createForm(CompraType::class, $compra);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $totalCompra = 0;

                // Manejar nuevos detalles y calcular subtotales
                foreach ($compra->getDetalleCompras() as $detalle) {
                    // Calcular subtotal para cada detalle
                    $subtotal = $detalle->getCantidad() * $detalle->getPrecioUnitario();
                    $detalle->setSubtotal($subtotal);

                    $totalCompra += $subtotal; // Acumular al total

                    // Si es un detalle nuevo, persistirlo y establecer la relación
                    if (!$originalDetalles->contains($detalle)) {
                        $detalle->setCompra($compra);
                        $entityManager->persist($detalle);
                    }
                }

                // Actualizar el total de la compra
                $compra->setTotal($totalCompra);

                // Manejar detalles eliminados
                foreach ($originalDetalles as $detalle) {
                    if (!$compra->getDetalleCompras()->contains($detalle)) {
                        $entityManager->remove($detalle);
                    }
                }

                $entityManager->flush();

                $this->addFlash('success', 'Compra actualizada correctamente');
                return $this->redirectToRoute('app_compra_index', [], Response::HTTP_SEE_OTHER);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al actualizar la compra: ' . $e->getMessage());
            }
        }

        return $this->render('compra/edit.html.twig', [
            'compra' => $compra,
            'form' => $form,
            'detalle_compras' => $compra->getDetalleCompras(),
        ]);
    }


    #[Route('/{id}', name: 'app_compra_delete', methods: ['POST'])]
    public function delete(Request $request, Compra $compra, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $compra->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($compra);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_compra_index', [], Response::HTTP_SEE_OTHER);
    }
}
