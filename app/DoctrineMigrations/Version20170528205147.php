<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170528205147 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE saison (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, start DATETIME NOT NULL, activated TINYINT(1) NOT NULL, reductions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', updated DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE licensee_saison (id INT AUTO_INCREMENT NOT NULL, licensee_id INT DEFAULT NULL, saison_id INT DEFAULT NULL, groupe_id INT DEFAULT NULL, start DATETIME NOT NULL, groupe_jours LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', inscription LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_C4F736EB734B22EE (licensee_id), INDEX IDX_C4F736EBF965414C (saison_id), INDEX IDX_C4F736EB7A45358C (groupe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE licensee_saison ADD CONSTRAINT FK_C4F736EB734B22EE FOREIGN KEY (licensee_id) REFERENCES licensee (id)');
        $this->addSql('ALTER TABLE licensee_saison ADD CONSTRAINT FK_C4F736EBF965414C FOREIGN KEY (saison_id) REFERENCES saison (id)');
        $this->addSql('ALTER TABLE licensee_saison ADD CONSTRAINT FK_C4F736EB7A45358C FOREIGN KEY (groupe_id) REFERENCES groupe (id)');
        $this->addSql('ALTER TABLE payment ADD saison_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DF965414C FOREIGN KEY (saison_id) REFERENCES saison (id)');
        $this->addSql('CREATE INDEX IDX_6D28840DF965414C ON payment (saison_id)');
        $this->addSql('ALTER TABLE licensee DROP date_licence');
        $this->addSql('ALTER TABLE sln_user DROP locked, DROP expired, DROP expires_at, DROP credentials_expired, DROP credentials_expire_at, CHANGE salt salt VARCHAR(255) DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DF965414C');
        $this->addSql('ALTER TABLE licensee_saison DROP FOREIGN KEY FK_C4F736EBF965414C');
        $this->addSql('DROP TABLE saison');
        $this->addSql('DROP TABLE licensee_saison');
        $this->addSql('ALTER TABLE licensee ADD date_licence DATE DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_6D28840DF965414C ON payment');
        $this->addSql('ALTER TABLE payment DROP saison_id');
        $this->addSql('ALTER TABLE sln_user ADD locked TINYINT(1) NOT NULL, ADD expired TINYINT(1) NOT NULL, ADD expires_at DATETIME DEFAULT NULL, ADD credentials_expired TINYINT(1) NOT NULL, ADD credentials_expire_at DATETIME DEFAULT NULL, CHANGE salt salt VARCHAR(255) NOT NULL, CHANGE confirmation_token confirmation_token VARCHAR(255) DEFAULT NULL');
    }
}
