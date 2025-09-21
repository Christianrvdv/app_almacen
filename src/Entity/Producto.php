<?php

namespace App\Entity;

use App\Repository\ProductoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    //#[ORM\ManyToOne(inversedBy: 'productos')]
    #[ORM\ManyToOne(targetEntity: Categoria::class, inversedBy: 'productos')]
    #[ORM\JoinColumn(name: 'categoria_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?Categoria $categoria = null;

    #[ORM\ManyToOne(inversedBy: 'productos')]
    private ?Proveedor $proveedor = null;

    /**
     * @var Collection<int, DetalleCompra>
     */
    #[ORM\OneToMany(targetEntity: DetalleCompra::class, mappedBy: 'producto')]
    private Collection $detalleCompras;

    /**
     * @var Collection<int, DetalleVenta>
     */
    #[ORM\OneToMany(targetEntity: DetalleVenta::class, mappedBy: 'producto')]
    private Collection $detalleVentas;

    /**
     * @var Collection<int, HistorialPrecios>
     */
    #[ORM\OneToMany(targetEntity: HistorialPrecios::class, mappedBy: 'producto')]
    private Collection $historialPrecios;

    /**
     * @var Collection<int, AjusteInventario>
     */
    #[ORM\OneToMany(targetEntity: AjusteInventario::class, mappedBy: 'producto')]
    private Collection $ajusteInventarios;

    public function __construct()
    {
        $this->detalleCompras = new ArrayCollection();
        $this->detalleVentas = new ArrayCollection();
        $this->historialPrecios = new ArrayCollection();
        $this->ajusteInventarios = new ArrayCollection();
    }

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

    public function getCategoria(): ?Categoria
    {
        return $this->categoria;
    }

    public function setCategoria(?Categoria $categoria): static
    {
        $this->categoria = $categoria;

        return $this;
    }

    public function getProveedor(): ?Proveedor
    {
        return $this->proveedor;
    }

    public function setProveedor(?Proveedor $proveedor): static
    {
        $this->proveedor = $proveedor;

        return $this;
    }

    /**
     * @return Collection<int, DetalleCompra>
     */
    public function getDetalleCompras(): Collection
    {
        return $this->detalleCompras;
    }

    public function addDetalleCompra(DetalleCompra $detalleCompra): static
    {
        if (!$this->detalleCompras->contains($detalleCompra)) {
            $this->detalleCompras->add($detalleCompra);
            $detalleCompra->setProducto($this);
        }

        return $this;
    }

    public function removeDetalleCompra(DetalleCompra $detalleCompra): static
    {
        if ($this->detalleCompras->removeElement($detalleCompra)) {
            // set the owning side to null (unless already changed)
            if ($detalleCompra->getProducto() === $this) {
                $detalleCompra->setProducto(null);
            }
        }

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
            $detalleVenta->setProducto($this);
        }

        return $this;
    }

    public function removeDetalleVenta(DetalleVenta $detalleVenta): static
    {
        if ($this->detalleVentas->removeElement($detalleVenta)) {
            // set the owning side to null (unless already changed)
            if ($detalleVenta->getProducto() === $this) {
                $detalleVenta->setProducto(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HistorialPrecios>
     */
    public function getHistorialPrecios(): Collection
    {
        return $this->historialPrecios;
    }

    public function addHistorialPrecio(HistorialPrecios $historialPrecio): static
    {
        if (!$this->historialPrecios->contains($historialPrecio)) {
            $this->historialPrecios->add($historialPrecio);
            $historialPrecio->setProducto($this);
        }

        return $this;
    }

    public function removeHistorialPrecio(HistorialPrecios $historialPrecio): static
    {
        if ($this->historialPrecios->removeElement($historialPrecio)) {
            // set the owning side to null (unless already changed)
            if ($historialPrecio->getProducto() === $this) {
                $historialPrecio->setProducto(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AjusteInventario>
     */
    public function getAjusteInventarios(): Collection
    {
        return $this->ajusteInventarios;
    }

    public function addAjusteInventario(AjusteInventario $ajusteInventario): static
    {
        if (!$this->ajusteInventarios->contains($ajusteInventario)) {
            $this->ajusteInventarios->add($ajusteInventario);
            $ajusteInventario->setProducto($this);
        }

        return $this;
    }

    public function removeAjusteInventario(AjusteInventario $ajusteInventario): static
    {
        if ($this->ajusteInventarios->removeElement($ajusteInventario)) {
            // set the owning side to null (unless already changed)
            if ($ajusteInventario->getProducto() === $this) {
                $ajusteInventario->setProducto(null);
            }
        }

        return $this;
    }
}
