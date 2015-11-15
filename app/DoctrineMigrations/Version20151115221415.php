<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151115221415 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE licensee_mail (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, title VARCHAR(250) NOT NULL, body LONGTEXT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_2DD65613F624B39D (sender_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE licenseemail_licensee (licenseemail_id INT NOT NULL, licensee_id INT NOT NULL, INDEX IDX_ED98875F35636BEF (licenseemail_id), INDEX IDX_ED98875F734B22EE (licensee_id), PRIMARY KEY(licenseemail_id, licensee_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE licenseemail_uploadfile (licenseemail_id INT NOT NULL, uploadfile_id VARCHAR(255) NOT NULL, INDEX IDX_6507A52435636BEF (licenseemail_id), INDEX IDX_6507A524E7D9CE57 (uploadfile_id), PRIMARY KEY(licenseemail_id, uploadfile_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE uploadfile (id VARCHAR(255) NOT NULL, user_id INT NOT NULL, filename VARCHAR(250) NOT NULL, filepath VARCHAR(4096) NOT NULL, inline TINYINT(1) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_FB3288F9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE licensee_mail ADD CONSTRAINT FK_2DD65613F624B39D FOREIGN KEY (sender_id) REFERENCES sln_user (id)');
        $this->addSql('ALTER TABLE licenseemail_licensee ADD CONSTRAINT FK_ED98875F35636BEF FOREIGN KEY (licenseemail_id) REFERENCES licensee_mail (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE licenseemail_licensee ADD CONSTRAINT FK_ED98875F734B22EE FOREIGN KEY (licensee_id) REFERENCES licensee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE licenseemail_uploadfile ADD CONSTRAINT FK_6507A52435636BEF FOREIGN KEY (licenseemail_id) REFERENCES licensee_mail (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE licenseemail_uploadfile ADD CONSTRAINT FK_6507A524E7D9CE57 FOREIGN KEY (uploadfile_id) REFERENCES uploadfile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE uploadfile ADD CONSTRAINT FK_FB3288F9A76ED395 FOREIGN KEY (user_id) REFERENCES sln_user (id)');
        $this->addSql('ALTER TABLE groupe ADD `order` INT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE licenseemail_licensee DROP FOREIGN KEY FK_ED98875F35636BEF');
        $this->addSql('ALTER TABLE licenseemail_uploadfile DROP FOREIGN KEY FK_6507A52435636BEF');
        $this->addSql('ALTER TABLE licenseemail_uploadfile DROP FOREIGN KEY FK_6507A524E7D9CE57');
        $this->addSql('DROP TABLE licensee_mail');
        $this->addSql('DROP TABLE licenseemail_licensee');
        $this->addSql('DROP TABLE licenseemail_uploadfile');
        $this->addSql('DROP TABLE uploadfile');
        $this->addSql('ALTER TABLE groupe DROP `order`');
    }
}
