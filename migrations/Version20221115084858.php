<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221115084858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game CHANGE game_launch_date game_launch_date DATETIME DEFAULT NULL, CHANGE game_description game_description VARCHAR(512) DEFAULT NULL, CHANGE genre genre VARCHAR(100) DEFAULT \'RPG\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game CHANGE game_launch_date game_launch_date DATETIME DEFAULT \'NULL\', CHANGE game_description game_description VARCHAR(512) DEFAULT \'NULL\', CHANGE genre genre VARCHAR(100) DEFAULT \'\'\'RPG\'\'\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}
