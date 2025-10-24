<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251019162152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ajuste_inventario (id INT AUTO_INCREMENT NOT NULL, producto_id INT DEFAULT NULL, tipo VARCHAR(255) NOT NULL, cantidad INT NOT NULL, fecha DATETIME NOT NULL, motivo VARCHAR(255) NOT NULL, usuario VARCHAR(255) NOT NULL, INDEX IDX_FBE2AA897645698E (producto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categoria (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, descripcion VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cliente (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, telefono VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, direccion VARCHAR(255) DEFAULT NULL, fecha_registro DATETIME NOT NULL, compra_totales NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compra (id INT AUTO_INCREMENT NOT NULL, proveedor_id INT DEFAULT NULL, fecha DATETIME NOT NULL, numero_factura VARCHAR(255) DEFAULT NULL, total NUMERIC(10, 2) NOT NULL, estado VARCHAR(255) DEFAULT NULL, observaciones VARCHAR(255) DEFAULT NULL, INDEX IDX_9EC131FFCB305D73 (proveedor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE detalle_compra (id INT AUTO_INCREMENT NOT NULL, compra_id INT DEFAULT NULL, producto_id INT DEFAULT NULL, cantidad INT NOT NULL, precio_unitario NUMERIC(10, 2) NOT NULL, subtotal NUMERIC(10, 2) NOT NULL, producto_nombre_historico VARCHAR(255) DEFAULT NULL, producto_codigo_barras_historico VARCHAR(100) DEFAULT NULL, producto_categoria_id_historico INT DEFAULT NULL, categoria_nombre_historico VARCHAR(255) DEFAULT NULL, precio_unitario_historico NUMERIC(10, 2) DEFAULT NULL, precio_costo_historico NUMERIC(10, 2) DEFAULT NULL, INDEX IDX_F219D258F2E704D7 (compra_id), INDEX IDX_F219D2587645698E (producto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE detalle_venta (id INT AUTO_INCREMENT NOT NULL, venta_id INT DEFAULT NULL, producto_id INT DEFAULT NULL, cantidad INT NOT NULL, precio_unitario NUMERIC(10, 2) NOT NULL, subtotal NUMERIC(10, 2) NOT NULL, producto_nombre_historico VARCHAR(255) DEFAULT NULL, producto_codigo_barras_historico VARCHAR(100) DEFAULT NULL, producto_categoria_id_historico INT DEFAULT NULL, categoria_nombre_historico VARCHAR(255) DEFAULT NULL, precio_unitario_historico NUMERIC(10, 2) DEFAULT NULL, precio_costo_historico NUMERIC(10, 2) DEFAULT NULL, INDEX IDX_5191A401F2A5805D (venta_id), INDEX IDX_5191A4017645698E (producto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE historial_precios (id INT AUTO_INCREMENT NOT NULL, producto_id INT DEFAULT NULL, tipo VARCHAR(255) NOT NULL, precio_anterior NUMERIC(10, 2) NOT NULL, precio_nuevo NUMERIC(10, 2) NOT NULL, fecha_cambio DATETIME NOT NULL, motivo VARCHAR(255) DEFAULT NULL, INDEX IDX_2071D9A67645698E (producto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE producto (id INT AUTO_INCREMENT NOT NULL, categoria_id INT DEFAULT NULL, proveedor_id INT DEFAULT NULL, nombre VARCHAR(255) NOT NULL, descipcion VARCHAR(255) DEFAULT NULL, codigo_barras VARCHAR(255) DEFAULT NULL, precio_compra NUMERIC(10, 2) NOT NULL, precio_venta_actual NUMERIC(10, 2) NOT NULL, stock_minimo INT NOT NULL, activo TINYINT(1) NOT NULL, fecha_creaccion DATETIME NOT NULL, fecha_actualizacion DATETIME NOT NULL, INDEX IDX_A7BB06153397707A (categoria_id), INDEX IDX_A7BB0615CB305D73 (proveedor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proveedor (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, telefono VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, direccion VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE venta (id INT AUTO_INCREMENT NOT NULL, cliente_id INT DEFAULT NULL, fecha DATETIME NOT NULL, total NUMERIC(10, 2) NOT NULL, tipo_veenta VARCHAR(255) NOT NULL, estado VARCHAR(255) NOT NULL, INDEX IDX_8FE7EE55DE734E51 (cliente_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ajuste_inventario ADD CONSTRAINT FK_FBE2AA897645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('ALTER TABLE compra ADD CONSTRAINT FK_9EC131FFCB305D73 FOREIGN KEY (proveedor_id) REFERENCES proveedor (id)');
        $this->addSql('ALTER TABLE detalle_compra ADD CONSTRAINT FK_F219D258F2E704D7 FOREIGN KEY (compra_id) REFERENCES compra (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE detalle_compra ADD CONSTRAINT FK_F219D2587645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('ALTER TABLE detalle_venta ADD CONSTRAINT FK_5191A401F2A5805D FOREIGN KEY (venta_id) REFERENCES venta (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE detalle_venta ADD CONSTRAINT FK_5191A4017645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('ALTER TABLE historial_precios ADD CONSTRAINT FK_2071D9A67645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('ALTER TABLE producto ADD CONSTRAINT FK_A7BB06153397707A FOREIGN KEY (categoria_id) REFERENCES categoria (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE producto ADD CONSTRAINT FK_A7BB0615CB305D73 FOREIGN KEY (proveedor_id) REFERENCES proveedor (id)');
        $this->addSql('ALTER TABLE venta ADD CONSTRAINT FK_8FE7EE55DE734E51 FOREIGN KEY (cliente_id) REFERENCES cliente (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ajuste_inventario DROP FOREIGN KEY FK_FBE2AA897645698E');
        $this->addSql('ALTER TABLE compra DROP FOREIGN KEY FK_9EC131FFCB305D73');
        $this->addSql('ALTER TABLE detalle_compra DROP FOREIGN KEY FK_F219D258F2E704D7');
        $this->addSql('ALTER TABLE detalle_compra DROP FOREIGN KEY FK_F219D2587645698E');
        $this->addSql('ALTER TABLE detalle_venta DROP FOREIGN KEY FK_5191A401F2A5805D');
        $this->addSql('ALTER TABLE detalle_venta DROP FOREIGN KEY FK_5191A4017645698E');
        $this->addSql('ALTER TABLE historial_precios DROP FOREIGN KEY FK_2071D9A67645698E');
        $this->addSql('ALTER TABLE producto DROP FOREIGN KEY FK_A7BB06153397707A');
        $this->addSql('ALTER TABLE producto DROP FOREIGN KEY FK_A7BB0615CB305D73');
        $this->addSql('ALTER TABLE venta DROP FOREIGN KEY FK_8FE7EE55DE734E51');
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
        $this->addSql('DROP TABLE messenger_messages');
    }
}
