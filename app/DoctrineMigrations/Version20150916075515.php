<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150916075515 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE licensee DROP inscription_ok, DROP attestation_ok, DROP photo_ok, DROP certificat_ok, DROP paiement_ok, DROP officiel, DROP bureau');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE licensee ADD inscription_ok TINYINT(1) NOT NULL, ADD attestation_ok TINYINT(1) NOT NULL, ADD photo_ok TINYINT(1) NOT NULL, ADD certificat_ok TINYINT(1) NOT NULL, ADD paiement_ok TINYINT(1) NOT NULL, ADD officiel TINYINT(1) NOT NULL, ADD bureau TINYINT(1) NOT NULL');
    }
}
