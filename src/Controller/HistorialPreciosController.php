<?php

namespace App\Controller;

use App\Entity\HistorialPrecios;
use App\Entity\Producto;
use App\Form\HistorialPreciosType;
use App\Repository\HistorialPreciosRepository;
use App\Repository\ProductoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/historial/precios')]
final class HistorialPreciosController extends AbstractController
{
    #[Route(name: 'app_historial_precios_index', methods: ['GET'])]
    public function index(HistorialPreciosRepository $historialPreciosRepository): Response
    {
        return $this->render('historial_precios/index.html.twig', [
            'historial_precios' => $historialPreciosRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_historial_precios_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Producto $producto): Response
    {
        $historialPrecio = new HistorialPrecios();
        $historialPrecio->setProducto($producto);

        $form = $this->createForm(HistorialPreciosType::class, $historialPrecio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($historialPrecio->getTipo() === "venta") {
                    $producto->setPrecioVentaActual($historialPrecio->getPrecioNuevo());
                } else {
                    $producto->setPrecioCompra($historialPrecio->getPrecioNuevo());
                }

                $entityManager->persist($historialPrecio);
                $entityManager->flush();


                return $this->redirectToRoute('app_historial_precios_index', [], Response::HTTP_SEE_OTHER);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al actualizar el precio: ' . $e->getMessage());
            }
        }

        return $this->render('historial_precios/new.html.twig', [
            'historial_precio' => $historialPrecio,
            'form' => $form,
            'producto' => $producto,
        ]);
    }

    #[Route('/{id}', name: 'app_historial_precios_show', methods: ['GET'])]
    public function show(HistorialPrecios $historialPrecio, HistorialPreciosRepository $historialPreciosRepository): Response
    {
        $esUltimoVenta = false;
        $esUltimoCompra = false;

        if ($historialPrecio->getProducto()) {
            $ultimoVenta = $historialPreciosRepository->findLastByProductAndType($historialPrecio->getProducto(), "venta");
            $ultimoCompra = $historialPreciosRepository->findLastByProductAndType($historialPrecio->getProducto(), "compra");

            $esUltimoVenta = $ultimoVenta && $ultimoVenta->getId() === $historialPrecio->getId();
            $esUltimoCompra = $ultimoCompra && $ultimoCompra->getId() === $historialPrecio->getId();
        }
        return $this->render('historial_precios/show.html.twig', [
            'historial_precio' => $historialPrecio,
            'ultimo_venta' => $esUltimoVenta,
            'ultimo_compra' => $esUltimoCompra,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_historial_precios_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, HistorialPrecios $historialPrecio, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HistorialPreciosType::class, $historialPrecio);
        $form->handleRequest($request);
        $producto = $historialPrecio->getProducto();

        if ($form->isSubmitted() && $form->isValid()) {

            if ($historialPrecio->getTipo() === "venta") {
                $producto->setPrecioVentaActual($historialPrecio->getPrecioNuevo());
            } else {
                $producto->setPrecioCompra($historialPrecio->getPrecioNuevo());
            }

            $entityManager->flush();
            return $this->redirectToRoute('app_historial_precios_show', [
                'id' => $historialPrecio->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('historial_precios/edit.html.twig', [
            'historial_precio' => $historialPrecio,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_historial_precios_delete', methods: ['POST'])]
    public function delete(Request $request, HistorialPrecios $historialPrecio, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $historialPrecio->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($historialPrecio);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_historial_precios_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_historial_precios_delete_new', methods: ['POST'])]
    public function deleteNew(
        Request                    $request,
        HistorialPrecios           $historialPrecio,
        EntityManagerInterface     $entityManager,
        HistorialPreciosRepository $historialPreciosRepository
    ): Response
    {
        if ($this->isCsrfTokenValid('delete' . $historialPrecio->getId(), $request->getPayload()->getString('_token'))) {
            $producto = $historialPrecio->getProducto();
            $tipo = $historialPrecio->getTipo();

            $registros = $historialPreciosRepository->createQueryBuilder('h')
                ->andWhere('h.producto = :producto')
                ->andWhere('h.tipo = :tipo')
                ->setParameter('producto', $producto)
                ->setParameter('tipo', $tipo)
                ->orderBy('h.fecha_cambio', 'DESC')
                ->setMaxResults(2)
                ->getQuery()
                ->getResult();

            if (count($registros) > 1) {
                $penultimoRegistro = $registros[1];
                $nuevoPrecio = $penultimoRegistro->getPrecioNuevo();
            } else {
                $nuevoPrecio = $historialPrecio->getPrecioAnterior();
            }

            if ($tipo === 'venta') {
                $producto->setPrecioVentaActual($nuevoPrecio);
            } else {
                $producto->setPrecioCompra($nuevoPrecio);
            }

            $entityManager->remove($historialPrecio);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_producto_show', [
            'id' => $producto->getId()
        ], Response::HTTP_SEE_OTHER);
    }
}
