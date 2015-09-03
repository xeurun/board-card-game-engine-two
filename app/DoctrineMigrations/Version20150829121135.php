<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use GameBundle\Constants\Ingridients;
use GameBundle\Constants\CardType;
use GameBundle\Constants\Potion;
use GameBundle\Constants\Mascot;
use GameBundle\Constants\Powder;
use GameBundle\Constants\Creation;
use GameBundle\Constants\Curse;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150829121135 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     * @throws AbortMigrationException
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->abortIf(!$this->connection->getSchemaManager()->tablesExist(array(self::TABLE)), sprintf('Table %s not exists', self::TABLE));

        foreach(self::ELEMENTS as $element) {
            $this->addSql(sprintf('INSERT INTO %s (name, image) VALUES (?, ?)', self::TABLE), $element);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->abortIf(!$this->connection->getSchemaManager()->tablesExist(array(self::TABLE)), sprintf('Table %s not exists', self::TABLE));

        foreach(self::ELEMENTS as $element) {
            $this->addSql(sprintf('DELETE FROM %s WHERE name = ?', self::TABLE), array($element[0]));
        }
    }
}
