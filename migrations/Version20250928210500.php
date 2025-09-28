<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250928210500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detalle_venta DROP FOREIGN KEY FK_5191A401F2A5805D');
        $this->addSql('ALTER TABLE detalle_venta ADD CONSTRAINT FK_5191A401F2A5805D FOREIGN KEY (venta_id) REFERENCES venta (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detalle_venta DROP FOREIGN KEY FK_5191A401F2A5805D');
        $this->addSql('ALTER TABLE detalle_venta ADD CONSTRAINT FK_5191A401F2A5805D FOREIGN KEY (venta_id) REFERENCES venta (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
