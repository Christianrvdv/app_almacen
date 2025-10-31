<?php

namespace App\Controller;

use App\Entity\DetalleVenta;
use App\Entity\Venta;
use App\Form\DetalleVentaType;
use App\Form\VentaType;
use App\Repository\VentaRepository;
use App\Service\CommonService;
use App\Service\TransactionService;
use App\Service\PdfGeneratorService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/venta')]
final class VentaController extends AbstractController
{
    public function __construct(
        private CommonService $commonService,
        private TransactionService $transactionService
    ){}

    /**
     * Método privado para inicializar una venta
     */
    private function initializeVenta(): Venta
    {
        $venta = new Venta();
        $venta->setFecha($this->commonService->getCurrentDateTime());
        return $venta;
    }

    /**
     * Método privado para manejar el formulario de venta
     */
    private function handleVentaForm(
        Request $request,
        EntityManagerInterface $entityManager,
        Venta $venta
    ): Response {
        $form = $this->createForm(VentaType::class, $venta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Usar el servicio para procesar la venta
                $this->transactionService->processVenta($venta);

                $entityManager->persist($venta);
                $entityManager->flush();

                $this->addFlash('success', 'Venta registrada exitosamente');
                return $this->redirectToRoute('app_venta_show', ['id' => $venta->getId()], Response::HTTP_SEE_OTHER);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al procesar la venta: ' . $e->getMessage());
            }
        }

        $detalleVenta = new DetalleVenta();
        $formDetalle = $this->createForm(DetalleVentaType::class, $detalleVenta);

        return $this->render('venta/new.html.twig', [
            'venta' => $venta,
            'form' => $form,
            'formDetalle' => $formDetalle,
        ]);
    }

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
        $venta = $this->initializeVenta();
        return $this->handleVentaForm($request, $entityManager, $venta);
    }

    #[Route('/{id}', name: 'app_venta_show', methods: ['GET'])]
    public function show(Venta $venta): Response
    {
        return $this->render('venta/show.html.twig', [
            'venta' => $venta,
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
                // Usar el servicio para manejar cambios
                $this->transactionService->handleDetailChanges(
                    $originalDetalles,
                    $venta->getDetalleVentas(),
                    $venta,
                    'venta'
                );

                // Procesar detalles actualizados
                $this->transactionService->processVenta($venta);

                $entityManager->flush();

                $this->addFlash('success', 'Venta actualizada correctamente');
                return $this->redirectToRoute('app_venta_show', ['id' => $venta->getId()], Response::HTTP_SEE_OTHER);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al actualizar la venta: ' . $e->getMessage());
            }
        }

        return $this->render('venta/edit.html.twig', [
            'venta' => $venta,
            'form' => $form,
            'detalle_ventas' => $venta->getDetalleVentas(),
        ]);
    }

    #[Route('/{id}', name: 'app_venta_delete', methods: ['POST'])]
    public function delete(Request $request, Venta $venta, EntityManagerInterface $entityManager): Response
    {
        // CORRECCIÓN: Cambiar toRfc4122() por simplemente el ID integer
        if ($this->isCsrfTokenValid('delete' . $venta->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($venta);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_venta_index', [], Response::HTTP_SEE_OTHER);
    }

    //PARA GESTIONAR LOS PDF
    #[Route('/{id}/pdf/generate', name: 'app_venta_generate_pdf', methods: ['GET'])]
    public function generatePdf(Venta $venta, PdfGeneratorService $pdfGenerator): Response
    {
        try {
            $filename = $pdfGenerator->generateInvoicePdf($venta);

            $this->addFlash('success', 'Factura generada correctamente: ' . $filename);
            return $this->redirectToRoute('app_venta_show', ['id' => $venta->getId()]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al generar el PDF: ' . $e->getMessage());
            return $this->redirectToRoute('app_venta_show', ['id' => $venta->getId()]);
        }
    }

    #[Route('/{id}/pdf/download', name: 'app_venta_download_pdf', methods: ['GET'])]
    public function downloadPdf(Venta $venta, PdfGeneratorService $pdfGenerator): Response
    {
        try {
            // CORRECCIÓN: Cambiar toRfc4122() por simplemente el ID integer
            $filename = 'factura_venta_' . $venta->getId() . '_' . date('Y-m-d') . '.pdf';
            $filePath = $pdfGenerator->getPdfFilePath($filename);

            if (!file_exists($filePath)) {
                // Si el archivo no existe, generarlo primero
                $filename = $pdfGenerator->generateInvoicePdf($venta);
                $filePath = $pdfGenerator->getPdfFilePath($filename);
            }

            $response = new Response(file_get_contents($filePath));
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            );
            $response->headers->set('Content-Disposition', $disposition);
            $response->headers->set('Content-Type', 'application/pdf');

            return $response;

        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al descargar el PDF: ' . $e->getMessage());
            return $this->redirectToRoute('app_venta_show', ['id' => $venta->getId()]);
        }
    }

    #[Route('/{id}/pdf/view', name: 'app_venta_view_pdf', methods: ['GET'])]
    public function viewPdf(Venta $venta, PdfGeneratorService $pdfGenerator): Response
    {
        try {
            // CORRECCIÓN: Cambiar toRfc4122() por simplemente el ID integer
            $filename = 'factura_venta_' . $venta->getId() . '_' . date('Y-m-d') . '.pdf';
            $filePath = $pdfGenerator->getPdfFilePath($filename);

            if (!file_exists($filePath)) {
                $filename = $pdfGenerator->generateInvoicePdf($venta);
                $filePath = $pdfGenerator->getPdfFilePath($filename);
            }

            $response = new Response(file_get_contents($filePath));
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');

            return $response;

        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al visualizar el PDF: ' . $e->getMessage());
            return $this->redirectToRoute('app_venta_show', ['id' => $venta->getId()]);
        }
    }
}
