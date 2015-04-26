<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150425223442 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE groupe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, categorie INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE licensee ADD groupe_id INT DEFAULT NULL, ADD inscription_ok TINYINT(1) NOT NULL, ADD paiement_ok TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE licensee ADD CONSTRAINT FK_8BE6BA6E7A45358C FOREIGN KEY (groupe_id) REFERENCES groupe (id)');
        $this->addSql('CREATE INDEX IDX_8BE6BA6E7A45358C ON licensee (groupe_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE licensee DROP FOREIGN KEY FK_8BE6BA6E7A45358C');
        $this->addSql('DROP TABLE groupe');
        $this->addSql('DROP INDEX IDX_8BE6BA6E7A45358C ON licensee');
        $this->addSql('ALTER TABLE licensee DROP groupe_id, DROP inscription_ok, DROP paiement_ok');
    }
}
