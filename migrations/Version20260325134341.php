<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325134341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card ADD game_id INT NOT NULL, ADD player_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D3E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('CREATE INDEX IDX_161498D3E48FD905 ON card (game_id)');
        $this->addSql('CREATE INDEX IDX_161498D399E6F5DF ON card (player_id)');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY `FK_232B318C99E6F5DF`');
        $this->addSql('DROP INDEX IDX_232B318C99E6F5DF ON game');
        $this->addSql('ALTER TABLE game DROP player_id');
        $this->addSql('ALTER TABLE player ADD is_human TINYINT NOT NULL, ADD game_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('CREATE INDEX IDX_98197A65E48FD905 ON player (game_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D3E48FD905');
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D399E6F5DF');
        $this->addSql('DROP INDEX IDX_161498D3E48FD905 ON card');
        $this->addSql('DROP INDEX IDX_161498D399E6F5DF ON card');
        $this->addSql('ALTER TABLE card DROP game_id, DROP player_id');
        $this->addSql('ALTER TABLE game ADD player_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT `FK_232B318C99E6F5DF` FOREIGN KEY (player_id) REFERENCES player (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_232B318C99E6F5DF ON game (player_id)');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65E48FD905');
        $this->addSql('DROP INDEX IDX_98197A65E48FD905 ON player');
        $this->addSql('ALTER TABLE player DROP is_human, DROP game_id');
    }
}
