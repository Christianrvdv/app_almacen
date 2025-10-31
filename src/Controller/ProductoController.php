<?php

namespace App\Controller;

use App\Entity\Producto;
use App\Form\ProductoType;
use App\Repository\ProductoRepository;
use App\Service\CommonService;
use App\Service\InventoryService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/producto')]
final class ProductoController extends AbstractController
{
    public function __construct(
        private InventoryService $inventoryService,
        private CommonService $commonService
    ) {}

    #[Route('', name: 'app_producto_index', methods: ['GET'])]
    public function index(Request $request, ProductoRepository $productoRepository, PaginatorInterface $paginator): Response
    {
        $query = $productoRepository->createQueryBuilder('p')
            ->leftJoin('p.categoria', 'c')
            ->addSelect('c')
            ->leftJoin('p.proveedor', 'prov')
            ->addSelect('prov')
            ->orderBy('p.fecha_actualizacion', 'DESC')
            ->getQuery();

        $productos = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('producto/index.html.twig', [
            'productos' => $productos,
        ]);
    }

    #[Route('/new', name: 'app_producto_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $producto = new Producto();
            $currentDateTime = $this->commonService->getCurrentDateTime();
            $producto->setFechaCreaccion($currentDateTime);
            $producto->setFechaActualizacion($currentDateTime);

            $form = $this->createForm(ProductoType::class, $producto);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($producto);
                $entityManager->flush();

                $this->addFlash('success', 'El producto ha sido creado correctamente.');
                return $this->redirectToRoute('app_producto_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('producto/new.html.twig', [
                'producto' => $producto,
                'form' => $form,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al crear el producto: ' . $e->getMessage());
            return $this->redirectToRoute('app_producto_index');
        }
    }

    #[Route('/{id}', name: 'app_producto_show', methods: ['GET'])]
    public function show(Producto $producto): Response
    {
        $stats = $this->inventoryService->calculateProductStats($producto);

        return $this->render('producto/show.html.twig', [
            'producto' => $producto,
            'stok' => $stats['stock'],
            'ingresos' => $stats['ingresos'],
            'ventas' => $stats['ventas'],
            'margen' => $stats['margen'],
            'modificaciones' => $stats['modificaciones'],
        ]);
    }

    #[Route('/{id}/edit', name: 'app_producto_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Producto $producto, EntityManagerInterface $entityManager): Response
    {
        try {
            $producto->setFechaActualizacion($this->commonService->getCurrentDateTime());

            $form = $this->createForm(ProductoType::class, $producto);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'El producto ha sido actualizado correctamente.');
                return $this->redirectToRoute('app_producto_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('producto/edit.html.twig', [
                'producto' => $producto,
                'form' => $form,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al actualizar el producto: ' . $e->getMessage());
            return $this->redirectToRoute('app_producto_index');
        }
    }

    #[Route('/{id}', name: 'app_producto_delete', methods: ['POST'])]
    public function delete(Request $request, Producto $producto, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete' . $producto->getId(), $request->getPayload()->getString('_token'))) {
                $entityManager->remove($producto);
                $entityManager->flush();
                $this->addFlash('success', 'El producto ha sido eliminado correctamente.');
            } else {
                $this->addFlash('error', 'Error de seguridad. No se pudo eliminar el producto.');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al eliminar el producto: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_producto_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/stats', name: 'app_producto_stats', methods: ['GET'])]
    public function getStats(Producto $producto): Response
    {
        $stats = $this->inventoryService->calculateProductStats($producto);

        return $this->json([
            'ventas_totales' => $stats['ventas'],
            'stock_actual' => $stats['stock'],
            'ingresos_generados' => $stats['ingresos'],
            'veces_modificado' => $stats['modificaciones'],
        ]);
    }
}
