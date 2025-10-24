<?php

namespace App\Entity;

use App\Repository\DetalleCompraRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DetalleCompraRepository::class)]
class DetalleCompra
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column]
    private ?int $cantidad = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $precio_unitario = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: false)]
    private ?string $subtotal = null;

    #[ORM\ManyToOne(inversedBy: 'detalleCompras')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Compra $compra = null;

    #[ORM\ManyToOne(inversedBy: 'detalleCompras')]
    private ?Producto $producto = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $producto_nombre_historico = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $producto_codigo_barras_historico = null;

    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    private ?string $producto_categoria_id_historico = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $categoria_nombre_historico  = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $precio_unitario_historico = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $precio_costo_historico = null;

    public function __construct()
    {
        $this->id = Uuid::v6();
    }

    public function getId(): ?Uuid
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

    public function getSubtotal(): ?float
    {
        return (string) ($this->getCantidad() * $this->getPrecioUnitario());
    }

    public function setSubtotal(?string $subtotal): void
    {
        $this->subtotal = $subtotal;
    }

    public function getCompra(): ?Compra
    {
        return $this->compra;
    }

    public function setCompra(?Compra $compra): static
    {
        $this->compra = $compra;

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

    public function getProductoNombreHistorico(): ?string
    {
        return $this->producto_nombre_historico;
    }

    public function setProductoNombreHistorico(?string $producto_nombre_historico): void
    {
        $this->producto_nombre_historico = $producto_nombre_historico;
    }

    public function getProductoCodigoBarrasHistorico(): ?string
    {
        return $this->producto_codigo_barras_historico;
    }

    public function setProductoCodigoBarrasHistorico(?string $producto_codigo_barras_historico): void
    {
        $this->producto_codigo_barras_historico = $producto_codigo_barras_historico;
    }

    public function getProductoCategoriaIdHistorico(): ?string
    {
        return $this->producto_categoria_id_historico;
    }

    public function setProductoCategoriaIdHistorico(?string $producto_categoria_id_historico): void
    {
        $this->producto_categoria_id_historico = $producto_categoria_id_historico;
    }

    public function getCategoriaNombreHistorico(): ?string
    {
        return $this->categoria_nombre_historico;
    }

    public function setCategoriaNombreHistorico(?string $categoria_nombre_historico): void
    {
        $this->categoria_nombre_historico = $categoria_nombre_historico;
    }

    public function getPrecioUnitarioHistorico(): ?float
    {
        return $this->precio_unitario_historico;
    }

    public function setPrecioUnitarioHistorico(?float $precio_unitario_historico): void
    {
        $this->precio_unitario_historico = $precio_unitario_historico;
    }

    public function getPrecioCostoHistorico(): ?float
    {
        return $this->precio_costo_historico;
    }

    public function setPrecioCostoHistorico(?float $precio_costo_historico): void
    {
        $this->precio_costo_historico = $precio_costo_historico;
    }

    public function __toString(): string
    {
        return $this->id ? $this->id->toRfc4122() : 'Nuevo Detalle Compra';
    }
}
