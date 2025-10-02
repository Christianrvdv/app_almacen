<?php

namespace App\Controller;

use App\Entity\DetalleVenta;
use App\Entity\Venta;
use App\Form\DetalleVentaType;
use App\Form\VentaEditType;
use App\Form\VentaType;
use App\Repository\VentaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/venta')]
final class VentaController extends AbstractController
{
    #[Route(name: 'app_venta_index', methods: ['GET'])]
    public function index(VentaRepository $ventaRepository): Response
    {
        return $this->render('venta/index.html.twig', [
            'ventas' => $ventaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_venta_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fecha_actual = new \DateTime('now',new \DateTimeZone('America/Toronto'));
        $venta = new Venta();
        $venta -> setFecha($fecha_actual);
        $form = $this->createForm(VentaType::class, $venta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($venta ->getDetalleVentas() as $detalleVenta) {
                $detalleVenta->setSubtotal(
                    $detalleVenta->getCantidad()*$detalleVenta->getPrecioUnitario());
                $entityManager->persist($detalleVenta);
            }
            $entityManager->persist($venta);
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_venta_index',
                ['id' => $venta->getId()],
                Response::HTTP_SEE_OTHER);
        }

        $detalleVenta = new DetalleVenta();
        $formDetalle = $this->createForm(DetalleVentaType::class, $detalleVenta);

        return $this->render('venta/new.html.twig', [
            'venta' => $venta,
            'form' => $form,
            'formDetalle' => $formDetalle,
        ]);
    }

    #[Route('/{id}', name: 'app_venta_show', methods: ['GET'])]
    public function show(Venta $venta): Response
    {
        $detalle_venta = $venta->getDetalleVentas();

        return $this->render('venta/show.html.twig', [
            'venta' => $venta,
            'detalle_venta' => $detalle_venta,

        ]);
    }
    #[Route('/{id}/edit', name: 'app_venta_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Venta $venta, EntityManagerInterface $entityManager): Response
    {
        // Guardar detalles originales antes del handleRequest
        $originalDetalles = new ArrayCollection();
        foreach ($venta->getDetalleVentas() as $detalle) {
            $originalDetalles->add($detalle);
        }

        $form = $this->createForm(VentaType::class, $venta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $totalVenta = 0;
                // Manejar nuevos detalles y calcular subtotales
                foreach ($venta->getDetalleVentas() as $detalle) {
                    // Calcular subtotal para cada detalle
                    $subtotal = $detalle->getCantidad() * $detalle->getPrecioUnitario();
                    $detalle->setSubtotal($subtotal);

                    $totalVenta += $subtotal; // Acumular al total

                    // Si es un detalle nuevo, persistirlo y establecer la relación
                    if (!$originalDetalles->contains($detalle)) {
                        $detalle->setVenta($venta);
                        $entityManager->persist($detalle);
                    }
                }

                // Actualizar el total de la venta
                $venta->setTotal($totalVenta);

                // Manejar detalles eliminados
                foreach ($originalDetalles as $detalle) {
                    if (!$venta->getDetalleVentas()->contains($detalle)) {
                        $entityManager->remove($detalle);
                    }
                }

                $entityManager->flush();

                $this->addFlash('success', 'Venta actualizada correctamente'); // ✅ Agregado
                return $this->redirectToRoute('app_venta_index', [], Response::HTTP_SEE_OTHER);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al actualizar la venta: ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted()) {
            // Manejo de errores de validación (mantener este buen patrón)
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('venta/edit.html.twig', [
            'venta' => $venta,
            'form' => $form,
            'detalle_ventas' => $venta->getDetalleVentas(), // ✅ Cambiado a plural para consistencia
        ]);
    }
    #[Route('/{id}', name: 'app_venta_delete', methods: ['POST'])]
    public function delete(Request $request, Venta $venta, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$venta->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($venta);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_venta_index', [], Response::HTTP_SEE_OTHER);
    }
}
