<?php

namespace App\Entity;

use App\Repository\DetalleCompraRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetalleCompraRepository::class)]
class DetalleCompra
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

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


}
