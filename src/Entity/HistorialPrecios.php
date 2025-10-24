<?php

namespace App\Entity;

use App\Repository\HistorialPreciosRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: HistorialPreciosRepository::class)]
class HistorialPrecios
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $tipo = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $precio_anterior = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $precio_nuevo = null;

    #[ORM\Column]
    private ?\DateTime $fecha_cambio = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motivo = null;

    #[ORM\ManyToOne(inversedBy: 'historialPrecios')]
    private ?Producto $producto = null;

    public function __construct()
    {
        $this->id = Uuid::v6();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getPrecioAnterior(): ?string
    {
        return $this->precio_anterior;
    }

    public function setPrecioAnterior(string $precio_anterior): static
    {
        $this->precio_anterior = $precio_anterior;

        return $this;
    }

    public function getPrecioNuevo(): ?string
    {
        return $this->precio_nuevo;
    }

    public function setPrecioNuevo(string $precio_nuevo): static
    {
        $this->precio_nuevo = $precio_nuevo;

        return $this;
    }

    public function getFechaCambio(): ?\DateTime
    {
        return $this->fecha_cambio;
    }

    public function setFechaCambio(\DateTime $fecha_cambio): static
    {
        $this->fecha_cambio = $fecha_cambio;

        return $this;
    }

    public function getMotivo(): ?string
    {
        return $this->motivo;
    }

    public function setMotivo(?string $motivo): static
    {
        $this->motivo = $motivo;

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

    public function __toString(): string
    {
        return $this->id ? $this->id->toRfc4122() : 'Nuevo Historial Precio';
    }
}
