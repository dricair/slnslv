<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170614193849 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE licensee_saison ADD groupe_new_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE licensee_saison ADD CONSTRAINT FK_C4F736EBD6384F89 FOREIGN KEY (groupe_new_id) REFERENCES groupe (id)');
        $this->addSql('CREATE INDEX IDX_C4F736EBD6384F89 ON licensee_saison (groupe_new_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE licensee_saison DROP FOREIGN KEY FK_C4F736EBD6384F89');
        $this->addSql('DROP INDEX IDX_C4F736EBD6384F89 ON licensee_saison');
        $this->addSql('ALTER TABLE licensee_saison DROP groupe_new_id');
    }
}
