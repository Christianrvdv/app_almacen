<?php

namespace App\Entity;

use App\Repository\ProductoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductoRepository::class)]
class Producto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descipcion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codigo_barras = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $precio_compra = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $precio_venta_actual = null;

    #[ORM\Column]
    private ?int $stock_minimo = null;

    #[ORM\Column]
    private ?bool $activo = null;

    #[ORM\Column]
    private ?\DateTime $fecha_creaccion = null;

    #[ORM\Column]
    private ?\DateTime $fecha_actualizacion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDescipcion(): ?string
    {
        return $this->descipcion;
    }

    public function setDescipcion(?string $descipcion): static
    {
        $this->descipcion = $descipcion;

        return $this;
    }

    public function getCodigoBarras(): ?string
    {
        return $this->codigo_barras;
    }

    public function setCodigoBarras(?string $codigo_barras): static
    {
        $this->codigo_barras = $codigo_barras;

        return $this;
    }

    public function getPrecioCompra(): ?string
    {
        return $this->precio_compra;
    }

    public function setPrecioCompra(string $precio_compra): static
    {
        $this->precio_compra = $precio_compra;

        return $this;
    }

    public function getPrecioVentaActual(): ?string
    {
        return $this->precio_venta_actual;
    }

    public function setPrecioVentaActual(string $precio_venta_actual): static
    {
        $this->precio_venta_actual = $precio_venta_actual;

        return $this;
    }

    public function getStockMinimo(): ?int
    {
        return $this->stock_minimo;
    }

    public function setStockMinimo(int $stock_minimo): static
    {
        $this->stock_minimo = $stock_minimo;

        return $this;
    }

    public function isActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): static
    {
        $this->activo = $activo;

        return $this;
    }

    public function getFechaCreaccion(): ?\DateTime
    {
        return $this->fecha_creaccion;
    }

    public function setFechaCreaccion(\DateTime $fecha_creaccion): static
    {
        $this->fecha_creaccion = $fecha_creaccion;

        return $this;
    }

    public function getFechaActualizacion(): ?\DateTime
    {
        return $this->fecha_actualizacion;
    }

    public function setFechaActualizacion(\DateTime $fecha_actualizacion): static
    {
        $this->fecha_actualizacion = $fecha_actualizacion;

        return $this;
    }
}
