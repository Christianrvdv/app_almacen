<?php
// src/Service/PdfGeneratorService.php

namespace App\Service;

use App\Entity\Venta;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;

class PdfGeneratorService
{
    private ParameterBagInterface $params;
    private Environment $twig;

    public function __construct(ParameterBagInterface $params, Environment $twig)
    {
        $this->params = $params;
        $this->twig = $twig;
    }

    /**
     * Genera el PDF y lo guarda en el servidor
     */
    public function generateInvoicePdf(Venta $venta): string
    {
        try {
            $pdfContent = $this->generateInvoicePdfContent($venta);

            // Crear directorio si no existe
            $pdfDirectory = $this->params->get('kernel.project_dir') . '/public/facturas/';
            if (!file_exists($pdfDirectory)) {
                mkdir($pdfDirectory, 0755, true);
            }

            $filename = 'factura_venta_' . $venta->getId() . '_' . date('Y-m-d') . '.pdf';
            $filePath = $pdfDirectory . $filename;

            // Guardar el PDF
            file_put_contents($filePath, $pdfContent);

            return $filename;

        } catch (\Exception $e) {
            throw new \RuntimeException('Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Genera el contenido PDF sin guardar archivo (más eficiente)
     */
    public function generateInvoicePdfContent(Venta $venta): string
    {
        try {
            // Configurar Dompdf
            $pdfOptions = new Options();
            $pdfOptions->set('defaultFont', 'Arial');
            $pdfOptions->set('isHtml5ParserEnabled', true);
            $pdfOptions->set('isRemoteEnabled', true);
            $pdfOptions->set('chroot', $this->params->get('kernel.project_dir'));

            $dompdf = new Dompdf($pdfOptions);

            // Generar el HTML de la factura usando Twig
            $html = $this->renderInvoiceHtml($venta);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return $dompdf->output();

        } catch (\Exception $e) {
            throw new \RuntimeException('Error al generar el contenido del PDF: ' . $e->getMessage());
        }
    }

    private function renderInvoiceHtml(Venta $venta): string
    {
        $detalleVentas = $venta->getDetalleVentas();

        if ($detalleVentas->isEmpty()) {
            throw new \InvalidArgumentException('La venta no tiene detalles');
        }

        $subtotal = 0;
        foreach ($detalleVentas as $detalle) {
            $subtotal += (float) $detalle->getSubtotal();
        }
        // CALCULAR IVA (16%)
        $iva = $subtotal * 0.16;
        $total = $subtotal + $iva;

        // CORRECCIÓN: Cambiar getTipoVeenta() por getTipoVenta()
        $tipoVenta = method_exists($venta, 'getTipoVenta') ?
            $venta->getTipoVenta() :
            ($venta->getTipoVenta() ?? 'No especificado');

        $vendedorNombre = 'Vendedor';
        $clienteNombre = $venta->getCliente() ? $venta->getCliente()->getNombre() : 'Cliente General';

        // Preparar los detalles para la plantilla
        $detallesArray = [];
        foreach ($detalleVentas as $detalle) {
            $detallesArray[] = [
                'productoNombre' => $detalle->getProducto()
                    ? $detalle->getProducto()->getNombre()
                    : ($detalle->getProductoNombreHistorico() ?? 'Producto no disponible'),
                'cantidad' => $detalle->getCantidad(),
                'precioUnitario' => (float) $detalle->getPrecioUnitario(),
                'subtotal' => (float) $detalle->getSubtotal(),
            ];
        }

        return $this->twig->render('pdf/factura.html.twig', [
            'venta' => $venta,
            'tipoVenta' => $tipoVenta,
            'vendedorNombre' => $vendedorNombre,
            'clienteNombre' => $clienteNombre,
            'detalleVentas' => $detallesArray,
            'subtotal' => $subtotal,
            'total' => $total,
            'iva' => $iva,
        ]);
    }

    public function getPdfFilePath(string $filename): string
    {
        return $this->params->get('kernel.project_dir') . '/public/facturas/' . $filename;
    }
}
