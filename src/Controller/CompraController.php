<?php

namespace App\Controller;

use App\Entity\Compra;
use App\Entity\DetalleCompra;
use App\Entity\Producto;
use App\Form\CompraType;
use App\Service\TransactionService;
use App\Form\DetalleCompraType;
use App\Repository\CompraRepository;
use App\Service\CommonService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/compra')]
final class CompraController extends AbstractController
{
    // Agregar el servicio al constructor
    public function __construct(
        private CommonService      $commonService,
        private TransactionService $transactionService
    )
    {
    }

    /**
     * Método privado para procesar detalles de compra
     */
    private function processCompraDetails(Compra $compra, EntityManagerInterface $entityManager): void
    {
        $this->transactionService->processCompra($compra);
    }

    #[Route(name: 'app_compra_index', methods: ['GET'])]
    public function index(CompraRepository $compraRepository): Response
    {
        return $this->render('compra/index.html.twig', [
            'compras' => $compraRepository->findAll(),
        ]);
    }

    /**
     * Método privado para inicializar una compra con valores por defecto
     */
    private function initializeCompra(?Producto $producto = null): Compra
    {
        $compra = new Compra();
        $compra->setFecha($this->commonService->getCurrentDateTime());

        if ($producto) {
            $compra->setProveedor($producto->getProveedor());

            // Crear y agregar el detalle a la compra
            $detalleCompra = new DetalleCompra();
            $detalleCompra->setProducto($producto);
            $detalleCompra->setPrecioUnitario($producto->getPrecioCompra());
            $detalleCompra->setCantidad(0);
            $detalleCompra->setSubtotal(0);

            $compra->addDetalleCompra($detalleCompra);
        }

        return $compra;
    }

    /**
     * Método privado para manejar el formulario de compra (elimina duplicación)
     */
    private function handleCompraForm(
        Request                $request,
        EntityManagerInterface $entityManager,
        Compra                 $compra
    ): Response
    {
        $form = $this->createForm(CompraType::class, $compra);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processCompraDetails($compra, $entityManager);

            $entityManager->persist($compra);
            $entityManager->flush();

            $this->addFlash('success', 'Compra registrada exitosamente');
            return $this->redirectToRoute('app_compra_show', ['id' => $compra->getId()], Response::HTTP_SEE_OTHER);
        }

        $detalleCompra = new DetalleCompra();
        $formDetalle = $this->createForm(DetalleCompraType::class, $detalleCompra);

        return $this->render('compra/new.html.twig', [
            'compra' => $compra,
            'form' => $form,
            'formDetalle' => $formDetalle,
        ]);
    }

    #[Route('/new', name: 'app_compra_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $compra = $this->initializeCompra();
        return $this->handleCompraForm($request, $entityManager, $compra);
    }

    #[Route('/new/{id}', name: 'app_compra_new_by_id', methods: ['GET', 'POST'])]
    public function newById(Request $request, EntityManagerInterface $entityManager, Producto $producto): Response
    {
        $compra = $this->initializeCompra($producto);
        return $this->handleCompraForm($request, $entityManager, $compra);
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

    // En el método edit, actualizar para usar el servicio
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
                // Usar el servicio para manejar cambios
                $this->transactionService->handleDetailChanges(
                    $originalDetalles,
                    $compra->getDetalleCompras(),
                    $compra,
                    'compra'
                );

                // Procesar detalles actualizados
                $this->transactionService->processCompra($compra);

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
        if ($this->isCsrfTokenValid('delete' . $compra->getId()->toRfc4122(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($compra);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_compra_index', [], Response::HTTP_SEE_OTHER);
    }
}
