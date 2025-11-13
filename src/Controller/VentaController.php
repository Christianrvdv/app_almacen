<?php

namespace App\Controller;

use App\Entity\DetalleVenta;
use App\Entity\Venta;
use App\Form\DetalleVentaType;
use App\Form\VentaType;
use App\Service\Core\PdfGeneratorService;
use App\Service\Venta\Interface\VentaServiceInterface;
use App\Service\Venta\Interface\VentaQueryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/venta')]
final class VentaController extends AbstractController
{
    public function __construct(
        private VentaQueryInterface $queryService,
        private VentaServiceInterface $operationsService
    ) {}

    #[Route(name: 'app_venta_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchResult = $this->queryService->searchAndPaginate($request);
        $statistics = $this->queryService->getStatistics();

        return $this->render('venta/index.html.twig', [
            'ventas' => $searchResult['pagination'],
            'totalVentas' => $statistics['totalVentas'],
            'totalCompletadas' => $statistics['totalCompletadas'],
            'totalPendientes' => $statistics['totalPendientes'],
            'totalIngresos' => $statistics['totalIngresos'],
            'searchTerm' => $searchResult['searchTerm'],
        ]);
    }

    #[Route('/new', name: 'app_venta_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $venta = $this->operationsService->initializeVenta();
        return $this->handleVentaForm($request, $venta, 'create');
    }

    #[Route('/{id}/edit', name: 'app_venta_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Venta $venta): Response
    {
        $originalDetalles = new ArrayCollection();
        foreach ($venta->getDetalleVentas() as $detalle) {
            $originalDetalles->add($detalle);
        }

        return $this->handleVentaForm($request, $venta, 'update', $originalDetalles);
    }

    private function handleVentaForm(
        Request $request,
        Venta $venta,
        string $operation,
        ArrayCollection $originalDetalles = null
    ): Response {
        $form = $this->createForm(VentaType::class, $venta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($operation === 'create') {
                    $this->operationsService->create($venta);
                    $message = 'La venta ha sido registrada correctamente.';
                } else {
                    $this->operationsService->update($venta, $originalDetalles);
                    $message = 'La venta ha sido actualizada correctamente.';
                }

                $this->addFlash('success', $message);
                return $this->redirectToRoute('app_venta_show', ['id' => $venta->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al procesar la venta: ' . $e->getMessage());
            }
        }

        $detalleVenta = new DetalleVenta();
        $formDetalle = $this->createForm(DetalleVentaType::class, $detalleVenta);

        $template = $operation === 'create' ? 'new.html.twig' : 'edit.html.twig';
        return $this->render("venta/{$template}", [
            'venta' => $venta,
            'form' => $form,
            'formDetalle' => $formDetalle,
            'detalle_ventas' => $venta->getDetalleVentas(),
        ]);
    }

    #[Route('/{id}', name: 'app_venta_delete', methods: ['POST'])]
    public function delete(Request $request, Venta $venta): Response
    {
        if ($this->isCsrfTokenValid('delete'.$venta->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $this->operationsService->delete($venta);
                $this->addFlash('success', 'La venta ha sido eliminada correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar la venta: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Error de seguridad. No se pudo eliminar la venta.');
        }

        return $this->redirectToRoute('app_venta_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_venta_show', methods: ['GET'])]
    public function show(Venta $venta): Response
    {
        return $this->render('venta/show.html.twig', [
            'venta' => $venta,
        ]);
    }

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
            $filename = 'factura_venta_' . $venta->getId() . '_' . date('Y-m-d') . '.pdf';
            $filePath = $pdfGenerator->getPdfFilePath($filename);

            if (!file_exists($filePath)) {
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
