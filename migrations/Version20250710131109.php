<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250710131109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE auth (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE humeur (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE moment (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, humeur_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', localisation VARCHAR(255) DEFAULT NULL, INDEX IDX_358C88A2A76ED395 (user_id), INDEX IDX_358C88A23E5E00A0 (humeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE moment_tag (moment_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_872326ABABE99143 (moment_id), INDEX IDX_872326ABBAD26311 (tag_id), PRIMARY KEY(moment_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, api_token VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL, is_verified TINYINT(1) NOT NULL, confirmation_token VARCHAR(64) DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D6497BA2F5EB (api_token), UNIQUE INDEX UNIQ_8D93D649C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE moment ADD CONSTRAINT FK_358C88A2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE moment ADD CONSTRAINT FK_358C88A23E5E00A0 FOREIGN KEY (humeur_id) REFERENCES humeur (id)');
        $this->addSql('ALTER TABLE moment_tag ADD CONSTRAINT FK_872326ABABE99143 FOREIGN KEY (moment_id) REFERENCES moment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE moment_tag ADD CONSTRAINT FK_872326ABBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE moment DROP FOREIGN KEY FK_358C88A2A76ED395');
        $this->addSql('ALTER TABLE moment DROP FOREIGN KEY FK_358C88A23E5E00A0');
        $this->addSql('ALTER TABLE moment_tag DROP FOREIGN KEY FK_872326ABABE99143');
        $this->addSql('ALTER TABLE moment_tag DROP FOREIGN KEY FK_872326ABBAD26311');
        $this->addSql('DROP TABLE auth');
        $this->addSql('DROP TABLE humeur');
        $this->addSql('DROP TABLE moment');
        $this->addSql('DROP TABLE moment_tag');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE user');
    }
}
