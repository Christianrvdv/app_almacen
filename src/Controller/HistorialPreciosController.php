<?php

namespace App\Controller;

use App\Entity\HistorialPrecios;
use App\Entity\Producto;
use App\Form\HistorialPreciosType;
use App\Repository\HistorialPreciosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/historial/precios')]
final class HistorialPreciosController extends AbstractController
{
    #[Route(name: 'app_historial_precios_index', methods: ['GET'])]
    public function index(Request $request, HistorialPreciosRepository $historialPreciosRepository, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('q', ''); // Obtener término de búsqueda

        // Construir query con filtro de búsqueda si existe
        $queryBuilder = $historialPreciosRepository->createQueryBuilder('h')
            ->leftJoin('h.producto', 'p')
            ->addSelect('p')
            ->orderBy('h.fecha_cambio', 'DESC');

        // Aplicar filtro de búsqueda si hay término
        if (!empty($searchTerm)) {
            $queryBuilder
                ->andWhere('h.tipo LIKE :searchTerm OR h.motivo LIKE :searchTerm OR p.nombre LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        $query = $queryBuilder->getQuery();

        $historialPrecios = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        // Obtener estadísticas totales (sin filtro)
        $totalRegistros = $historialPreciosRepository->count([]);
        $totalVenta = $historialPreciosRepository->count(['tipo' => 'venta']);
        $totalCompra = $historialPreciosRepository->count(['tipo' => 'compra']);
        $totalAjustePromo = $historialPreciosRepository->createQueryBuilder('h')
            ->select('COUNT(h.id)')
            ->where("h.tipo IN ('promocion', 'ajuste')")
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('historial_precios/index.html.twig', [
            'historial_precios' => $historialPrecios,
            'searchTerm' => $searchTerm,
            'totalRegistros' => $totalRegistros,
            'totalVenta' => $totalVenta,
            'totalCompra' => $totalCompra,
            'totalAjustePromo' => $totalAjustePromo,
        ]);
    }

    #[Route('/{id}', name: 'app_historial_precios_show', methods: ['GET'])]
    public function show(HistorialPrecios $historialPrecio, HistorialPreciosRepository $historialPreciosRepository): Response
    {
        $ultimoVenta = $historialPreciosRepository->findLastByProductAndType($historialPrecio->getProducto(), 'venta');
        $ultimoCompra = $historialPreciosRepository->findLastByProductAndType($historialPrecio->getProducto(), 'compra');
        $esUltimoVenta = $ultimoVenta && $ultimoVenta->getId()->equals($historialPrecio->getId());
        $esUltimoCompra = $ultimoCompra && $ultimoCompra->getId()->equals($historialPrecio->getId());

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

            try {
                if ($historialPrecio->getTipo() === "venta") {
                    $producto->setPrecioVentaActual($historialPrecio->getPrecioNuevo());
                } else {
                    $producto->setPrecioCompra($historialPrecio->getPrecioNuevo());
                }

                $entityManager->flush();

                $this->addFlash('success', 'El historial de precios ha sido actualizado correctamente y el precio del producto ha sido actualizado.');

                return $this->redirectToRoute('app_historial_precios_show', [
                    'id' => $historialPrecio->getId()
                ], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al actualizar el historial de precios: ' . $e->getMessage());
            }
        }

        return $this->render('historial_precios/edit.html.twig', [
            'historial_precio' => $historialPrecio,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_historial_precios_delete_new', methods: ['POST'])]
    public function deleteNew(
        Request                    $request,
        HistorialPrecios           $historialPrecio,
        EntityManagerInterface     $entityManager,
        HistorialPreciosRepository $historialPreciosRepository
    ): Response
    {
        if ($this->isCsrfTokenValid('delete' . $historialPrecio->getId()->toRfc4122(), $request->getPayload()->getString('_token'))) {
            try {
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

                $this->addFlash('success', 'El historial de precios ha sido eliminado correctamente y el precio del producto ha sido revertido.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar el historial de precios: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token de seguridad inválido, no se pudo eliminar el historial de precios.');
        }

        return $this->redirectToRoute('app_producto_show', [
            'id' => $producto->getId()
        ], Response::HTTP_SEE_OTHER);
    }
}
