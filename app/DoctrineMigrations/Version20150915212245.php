<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL\Schema\Schema;
use SLN\RegisterBundle\Entity\Licensee;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150915212245 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE licensee ADD fonctions LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', ADD inscription LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('UPDATE licensee SET fonctions = \'a:0:{}\'');
        $this->addSql('UPDATE licensee SET inscription = \'a:0:{}\'');
    }

    /**
     * Migrate data: officiel/bureau change to 'function' array field
     */
    public function postUp(Schema $schema) {
        echo "Postup migration\n"; 
        $em = $this->container->get('doctrine.orm.entity_manager');
        foreach ($em->getRepository('SLNRegisterBundle:Licensee')->findAll() as $licensee) {
            $fonctions = array();
            if ($licensee->getOfficiel()) $fonctions[] = Licensee::OFFICIEL;
            if ($licensee->getBureau())   $fonctions[] = Licensee::BUREAU;

            echo "- Updating data for {$licensee->getPrenom()} {$licensee->getNom()}\n";
            $licensee->setFonctions($fonctions);

            $em->persist($licensee);
        }

        $em->flush();
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE licensee DROP fonctions, DROP inscription');
    }
}
