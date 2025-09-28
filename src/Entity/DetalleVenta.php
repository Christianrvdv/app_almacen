<?php

namespace App\Entity;

use App\Repository\DetalleVentaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetalleVentaRepository::class)]
class DetalleVenta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $cantidad = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $precio_unitario = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $precio_costo = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $subtotal = null;

    //#[ORM\ManyToOne(inversedBy: 'detalleVentas')]
    #[ORM\ManyToOne(targetEntity: Venta::class, inversedBy: 'detalleVentas')]
    #[ORM\JoinColumn(name: 'venta_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Venta $venta = null;

    #[ORM\ManyToOne(inversedBy: 'detalleVentas')]
    private ?Producto $producto = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $producto_nombre_historico = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $producto_codigo_barras_historico = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $producto_categoria_id_historico = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $categoria_nombre_historico  = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $precio_unitario_historico = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $precio_costo_historico = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getPrecioUnitario(): ?string
    {
        return $this->precio_unitario;
    }

    public function setPrecioUnitario(string $precio_unitario): static
    {
        $this->precio_unitario = $precio_unitario;

        return $this;
    }

    public function getPrecioCosto(): ?string
    {
        return $this->precio_costo;
    }

    public function setPrecioCosto(string $precio_costo): static
    {
        $this->precio_costo = $precio_costo;

        return $this;
    }

    public function getSubtotal(): ?string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string $subtotal): static
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getVenta(): ?Venta
    {
        return $this->venta;
    }

    public function setVenta(?Venta $venta): static
    {
        $this->venta = $venta;

        return $this;
    }

    public function getProducto(): ?Producto
    {
        return $this->producto;
    }

    public function setProducto(?Producto $producto): static
    {
        $this->producto = $producto;

        return $this;
    }

    public function getPrecioCostoHistorico(): ?float
    {
        return $this->precio_costo_historico;
    }

    public function setPrecioCostoHistorico(?float $precio_costo_historico): void
    {
        $this->precio_costo_historico = $precio_costo_historico;
    }

    public function getPrecioUnitarioHistorico(): ?float
    {
        return $this->precio_unitario_historico;
    }

    public function setPrecioUnitarioHistorico(?float $precio_unitario_historico): void
    {
        $this->precio_unitario_historico = $precio_unitario_historico;
    }

    public function getCategoriaNombreHistorico(): ?string
    {
        return $this->categoria_nombre_historico;
    }

    public function setCategoriaNombreHistorico(?string $categoria_nombre_historico): void
    {
        $this->categoria_nombre_historico = $categoria_nombre_historico;
    }

    public function getProductoCategoriaIdHistorico(): ?int
    {
        return $this->producto_categoria_id_historico;
    }

    public function setProductoCategoriaIdHistorico(?int $producto_categoria_id_historico): void
    {
        $this->producto_categoria_id_historico = $producto_categoria_id_historico;
    }

    public function getProductoCodigoBarrasHistorico(): ?string
    {
        return $this->producto_codigo_barras_historico;
    }

    public function setProductoCodigoBarrasHistorico(?string $producto_codigo_barras_historico): void
    {
        $this->producto_codigo_barras_historico = $producto_codigo_barras_historico;
    }

    public function getProductoNombreHistorico(): ?string
    {
        return $this->producto_nombre_historico;
    }

    public function setProductoNombreHistorico(?string $producto_nombre_historico): void
    {
        $this->producto_nombre_historico = $producto_nombre_historico;
    }
}
