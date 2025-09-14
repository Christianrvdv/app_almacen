<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250914223315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ajuste_inventario (id INT AUTO_INCREMENT NOT NULL, tipo VARCHAR(255) NOT NULL, cantidad INT NOT NULL, fecha DATETIME NOT NULL, motivo VARCHAR(255) NOT NULL, usuario VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categoria (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, descripcion VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cliente (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, telefono VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, direccion VARCHAR(255) DEFAULT NULL, fecha_registro DATETIME NOT NULL, compra_totales NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compra (id INT AUTO_INCREMENT NOT NULL, fecha DATETIME NOT NULL, numero_factura VARCHAR(255) DEFAULT NULL, total NUMERIC(10, 2) NOT NULL, estado VARCHAR(255) DEFAULT NULL, observaciones VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE detalle_compra (id INT AUTO_INCREMENT NOT NULL, cantidad INT NOT NULL, precio_unitario NUMERIC(10, 2) NOT NULL, subtotal NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE detalle_venta (id INT AUTO_INCREMENT NOT NULL, cantidad INT NOT NULL, precio_unitario NUMERIC(10, 2) NOT NULL, precio_costo NUMERIC(10, 2) NOT NULL, subtotal NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE historial_precios (id INT AUTO_INCREMENT NOT NULL, tipo VARCHAR(255) NOT NULL, precio_anterior NUMERIC(10, 2) NOT NULL, precio_nuevo NUMERIC(10, 2) NOT NULL, fecha_cambio DATETIME NOT NULL, motivo VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE producto (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, descipcion VARCHAR(255) DEFAULT NULL, codigo_barras VARCHAR(255) DEFAULT NULL, precio_compra NUMERIC(10, 2) NOT NULL, precio_venta_actual NUMERIC(10, 2) NOT NULL, stock_minimo INT NOT NULL, activo TINYINT(1) NOT NULL, fecha_creaccion DATETIME NOT NULL, fecha_actualizacion DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proveedor (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, telefono VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, direccion VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE venta (id INT AUTO_INCREMENT NOT NULL, fecha DATETIME NOT NULL, total NUMERIC(10, 2) NOT NULL, tipo_veenta VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE ajuste_inventario');
        $this->addSql('DROP TABLE categoria');
        $this->addSql('DROP TABLE cliente');
        $this->addSql('DROP TABLE compra');
        $this->addSql('DROP TABLE detalle_compra');
        $this->addSql('DROP TABLE detalle_venta');
        $this->addSql('DROP TABLE historial_precios');
        $this->addSql('DROP TABLE producto');
        $this->addSql('DROP TABLE proveedor');
        $this->addSql('DROP TABLE venta');
    }
}
