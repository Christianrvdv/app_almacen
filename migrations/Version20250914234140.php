<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250914234140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ajuste_inventario ADD producto_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ajuste_inventario ADD CONSTRAINT FK_FBE2AA897645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('CREATE INDEX IDX_FBE2AA897645698E ON ajuste_inventario (producto_id)');
        $this->addSql('ALTER TABLE compra ADD proveedor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE compra ADD CONSTRAINT FK_9EC131FFCB305D73 FOREIGN KEY (proveedor_id) REFERENCES compra (id)');
        $this->addSql('CREATE INDEX IDX_9EC131FFCB305D73 ON compra (proveedor_id)');
        $this->addSql('ALTER TABLE detalle_compra ADD compra_id INT DEFAULT NULL, ADD producto_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE detalle_compra ADD CONSTRAINT FK_F219D258F2E704D7 FOREIGN KEY (compra_id) REFERENCES compra (id)');
        $this->addSql('ALTER TABLE detalle_compra ADD CONSTRAINT FK_F219D2587645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('CREATE INDEX IDX_F219D258F2E704D7 ON detalle_compra (compra_id)');
        $this->addSql('CREATE INDEX IDX_F219D2587645698E ON detalle_compra (producto_id)');
        $this->addSql('ALTER TABLE detalle_venta ADD venta_id INT DEFAULT NULL, ADD producto_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE detalle_venta ADD CONSTRAINT FK_5191A401F2A5805D FOREIGN KEY (venta_id) REFERENCES venta (id)');
        $this->addSql('ALTER TABLE detalle_venta ADD CONSTRAINT FK_5191A4017645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('CREATE INDEX IDX_5191A401F2A5805D ON detalle_venta (venta_id)');
        $this->addSql('CREATE INDEX IDX_5191A4017645698E ON detalle_venta (producto_id)');
        $this->addSql('ALTER TABLE historial_precios ADD producto_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE historial_precios ADD CONSTRAINT FK_2071D9A67645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('CREATE INDEX IDX_2071D9A67645698E ON historial_precios (producto_id)');
        $this->addSql('ALTER TABLE producto ADD categoria_id INT DEFAULT NULL, ADD proveedor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE producto ADD CONSTRAINT FK_A7BB06153397707A FOREIGN KEY (categoria_id) REFERENCES categoria (id)');
        $this->addSql('ALTER TABLE producto ADD CONSTRAINT FK_A7BB0615CB305D73 FOREIGN KEY (proveedor_id) REFERENCES proveedor (id)');
        $this->addSql('CREATE INDEX IDX_A7BB06153397707A ON producto (categoria_id)');
        $this->addSql('CREATE INDEX IDX_A7BB0615CB305D73 ON producto (proveedor_id)');
        $this->addSql('ALTER TABLE venta ADD cliente_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE venta ADD CONSTRAINT FK_8FE7EE55DE734E51 FOREIGN KEY (cliente_id) REFERENCES cliente (id)');
        $this->addSql('CREATE INDEX IDX_8FE7EE55DE734E51 ON venta (cliente_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE ajuste_inventario DROP FOREIGN KEY FK_FBE2AA897645698E');
        $this->addSql('DROP INDEX IDX_FBE2AA897645698E ON ajuste_inventario');
        $this->addSql('ALTER TABLE ajuste_inventario DROP producto_id');
        $this->addSql('ALTER TABLE detalle_compra DROP FOREIGN KEY FK_F219D258F2E704D7');
        $this->addSql('ALTER TABLE detalle_compra DROP FOREIGN KEY FK_F219D2587645698E');
        $this->addSql('DROP INDEX IDX_F219D258F2E704D7 ON detalle_compra');
        $this->addSql('DROP INDEX IDX_F219D2587645698E ON detalle_compra');
        $this->addSql('ALTER TABLE detalle_compra DROP compra_id, DROP producto_id');
        $this->addSql('ALTER TABLE venta DROP FOREIGN KEY FK_8FE7EE55DE734E51');
        $this->addSql('DROP INDEX IDX_8FE7EE55DE734E51 ON venta');
        $this->addSql('ALTER TABLE venta DROP cliente_id');
        $this->addSql('ALTER TABLE producto DROP FOREIGN KEY FK_A7BB06153397707A');
        $this->addSql('ALTER TABLE producto DROP FOREIGN KEY FK_A7BB0615CB305D73');
        $this->addSql('DROP INDEX IDX_A7BB06153397707A ON producto');
        $this->addSql('DROP INDEX IDX_A7BB0615CB305D73 ON producto');
        $this->addSql('ALTER TABLE producto DROP categoria_id, DROP proveedor_id');
        $this->addSql('ALTER TABLE compra DROP FOREIGN KEY FK_9EC131FFCB305D73');
        $this->addSql('DROP INDEX IDX_9EC131FFCB305D73 ON compra');
        $this->addSql('ALTER TABLE compra DROP proveedor_id');
        $this->addSql('ALTER TABLE detalle_venta DROP FOREIGN KEY FK_5191A401F2A5805D');
        $this->addSql('ALTER TABLE detalle_venta DROP FOREIGN KEY FK_5191A4017645698E');
        $this->addSql('DROP INDEX IDX_5191A401F2A5805D ON detalle_venta');
        $this->addSql('DROP INDEX IDX_5191A4017645698E ON detalle_venta');
        $this->addSql('ALTER TABLE detalle_venta DROP venta_id, DROP producto_id');
        $this->addSql('ALTER TABLE historial_precios DROP FOREIGN KEY FK_2071D9A67645698E');
        $this->addSql('DROP INDEX IDX_2071D9A67645698E ON historial_precios');
        $this->addSql('ALTER TABLE historial_precios DROP producto_id');
    }
}
