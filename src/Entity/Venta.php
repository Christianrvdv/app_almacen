<?php

namespace App\Entity;

use App\Repository\VentaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VentaRepository::class)]
class Venta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $fecha = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $total = null;

    #[ORM\Column(length: 255)]
    private ?string $tipo_veenta = null;

    #[ORM\ManyToOne(inversedBy: 'ventas')]
    private ?Cliente $cliente = null;

    /**
     * @var Collection<int, DetalleVenta>
     */
    //#[ORM\OneToMany(targetEntity: DetalleVenta::class, mappedBy: 'venta')]
    #[ORM\OneToMany(targetEntity: DetalleVenta::class, mappedBy: 'venta', cascade: ["persist"])]
    private Collection $detalleVentas;

    #[ORM\Column(length: 255)]
    private ?string $estado = null;

    public function __construct()
    {
        $this->detalleVentas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFecha(): ?\DateTime
    {
        return $this->fecha;
    }

    public function setFecha(\DateTime $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getTipoVeenta(): ?string
    {
        return $this->tipo_veenta;
    }

    public function setTipoVeenta(string $tipo_veenta): static
    {
        $this->tipo_veenta = $tipo_veenta;

        return $this;
    }

    public function getCliente(): ?Cliente
    {
        return $this->cliente;
    }

    public function setCliente(?Cliente $cliente): static
    {
        $this->cliente = $cliente;

        return $this;
    }

    /**
     * @return Collection<int, DetalleVenta>
     */
    public function getDetalleVentas(): Collection
    {
        return $this->detalleVentas;
    }

    public function addDetalleVenta(DetalleVenta $detalleVenta): static
    {
        if (!$this->detalleVentas->contains($detalleVenta)) {
            $this->detalleVentas->add($detalleVenta);
            $detalleVenta->setVenta($this);
        }

        return $this;
    }

    public function removeDetalleVenta(DetalleVenta $detalleVenta): static
    {
        if ($this->detalleVentas->removeElement($detalleVenta)) {
            // set the owning side to null (unless already changed)
            if ($detalleVenta->getVenta() === $this) {
                $detalleVenta->setVenta(null);
            }
        }

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }
}
