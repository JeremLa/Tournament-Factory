<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180621152658 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tftournament ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tftournament ADD CONSTRAINT FK_BA2DF0307E3C61F9 FOREIGN KEY (owner_id) REFERENCES tfuser (id)');
        $this->addSql('CREATE INDEX IDX_BA2DF0307E3C61F9 ON tftournament (owner_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tftournament DROP FOREIGN KEY FK_BA2DF0307E3C61F9');
        $this->addSql('DROP INDEX IDX_BA2DF0307E3C61F9 ON tftournament');
        $this->addSql('ALTER TABLE tftournament DROP owner_id');
    }
}
