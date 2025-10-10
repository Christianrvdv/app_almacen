<?php
// src/Service/PdfGeneratorService.php

namespace App\Service;

use App\Entity\Venta;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PdfGeneratorService
{
    private ParameterBagInterface $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function generateInvoicePdf(Venta $venta): string
    {
        // Configurar Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($pdfOptions);

        // Generar el HTML de la factura
        $html = $this->renderInvoiceHtml($venta);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Crear directorio si no existe
        $pdfDirectory = $this->params->get('kernel.project_dir') . '/public/facturas/';
        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0777, true);
        }

        $filename = 'factura_venta_' . $venta->getId() . '_' . date('Y-m-d') . '.pdf';
        $filePath = $pdfDirectory . $filename;

        // Guardar el PDF
        file_put_contents($filePath, $dompdf->output());

        return $filename;
    }

    private function renderInvoiceHtml(Venta $venta): string
    {
        $detalleVentas = $venta->getDetalleVentas();
        $subtotal = 0;

        foreach ($detalleVentas as $detalle) {
            $subtotal += (float) $detalle->getSubtotal();
        }

        $iva = $subtotal * 0.16; // 16% IVA (ajustar según tu país)
        $total = $subtotal + $iva;

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Factura #{$venta->getId()}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                .company-info { float: left; width: 50%; }
                .invoice-info { float: right; width: 40%; text-align: right; }
                .clear { clear: both; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .totals { float: right; width: 300px; margin-top: 20px; }
                .totals table { width: 100%; }
                .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>FACTURA</h1>
            </div>

            <div class='company-info'>
                <h3>EMPRESA XYZ</h3>
                <p>Dirección: Calle Principal #123</p>
                <p>Teléfono: (123) 456-7890</p>
                <p>RFC: XYZ123456789</p>
            </div>

            <div class='invoice-info'>
                <p><strong>Factura #:</strong> {$venta->getId()}</p>
                <p><strong>Fecha:</strong> {$venta->getFecha()->format('d/m/Y')}</p>
                <p><strong>Método Pago:</strong> " . ucfirst($venta->getTipoVeenta()) . "</p>
                <p><strong>Estado:</strong> " . ucfirst($venta->getEstado()) . "</p>
            </div>

            <div class='clear'></div>

            <div class='client-info'>
                <h3>Datos del Cliente</h3>
                <p><strong>Nombre:</strong> " . ($venta->getCliente() ? $venta->getCliente()->getNombre() : 'Cliente General') . "</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    " . $this->renderInvoiceItems($detalleVentas) . "
                </tbody>
            </table>

            <div class='totals'>
                <table>
                    <tr>
                        <td><strong>Subtotal:</strong></td>
                        <td>$" . number_format($subtotal, 2) . "</td>
                    </tr>
                    <tr>
                        <td><strong>IVA (16%):</strong></td>
                        <td>$" . number_format($iva, 2) . "</td>
                    </tr>
                    <tr>
                        <td><strong>Total:</strong></td>
                        <td><strong>$" . number_format($total, 2) . "</strong></td>
                    </tr>
                </table>
            </div>

            <div class='clear'></div>

            <div class='footer'>
                <p>¡Gracias por su compra!</p>
                <p>Este documento es una factura generada electrónicamente</p>
            </div>
        </body>
        </html>
        ";
    }

    private function renderInvoiceItems($detalleVentas): string
    {
        $itemsHtml = '';
        foreach ($detalleVentas as $detalle) {
            $productoNombre = $detalle->getProducto()
                ? $detalle->getProducto()->getNombre()
                : $detalle->getProductoNombreHistorico();

            $itemsHtml .= "
            <tr>
                <td>{$productoNombre}</td>
                <td>{$detalle->getCantidad()}</td>
                <td>$" . number_format((float) $detalle->getPrecioUnitario(), 2) . "</td>
                <td>$" . number_format((float) $detalle->getSubtotal(), 2) . "</td>
            </tr>
            ";
        }
        return $itemsHtml;
    }

    public function getPdfFilePath(string $filename): string
    {
        return $this->params->get('kernel.project_dir') . '/public/facturas/' . $filename;
    }
}
