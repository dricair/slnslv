<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170605202238 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE licensee_mail ADD saison_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE licensee_mail ADD CONSTRAINT FK_2DD65613F965414C FOREIGN KEY (saison_id) REFERENCES saison (id)');
        $this->addSql('CREATE INDEX IDX_2DD65613F965414C ON licensee_mail (saison_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE licensee_mail DROP FOREIGN KEY FK_2DD65613F965414C');
        $this->addSql('DROP INDEX IDX_2DD65613F965414C ON licensee_mail');
        $this->addSql('ALTER TABLE licensee_mail DROP saison_id');
    }
}
