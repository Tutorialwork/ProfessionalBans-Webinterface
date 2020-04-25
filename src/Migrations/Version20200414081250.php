<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200414081250 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE bans (uuid VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, banned INT NOT NULL, muted INT NOT NULL, reason VARCHAR(255) NOT NULL, end INT NOT NULL, teamuuid VARCHAR(255) NOT NULL, bans INT NOT NULL, mutes INT NOT NULL, firstlogin VARCHAR(255) NOT NULL, lastlogin VARCHAR(255) NOT NULL, online_status INT NOT NULL, online_time INT NOT NULL, PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invite (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, creator INT NOT NULL, creationdate DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reasons (id INT AUTO_INCREMENT NOT NULL, reason VARCHAR(255) NOT NULL, time INT NOT NULL, type TINYINT(1) NOT NULL, added_at DATETIME NOT NULL, bans INT NOT NULL, perms VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, auth TINYINT(1) NOT NULL, authcode VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE bans');
        $this->addSql('DROP TABLE invite');
        $this->addSql('DROP TABLE reasons');
        $this->addSql('DROP TABLE user');
    }
}
