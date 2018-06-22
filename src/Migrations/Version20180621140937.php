<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180621140937 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tfteam (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_D2A7159AE7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tfteam_tftournament (tfteam_id INT NOT NULL, tftournament_id INT NOT NULL, INDEX IDX_D95D1D21E9C2289B (tfteam_id), INDEX IDX_D95D1D2113499DD2 (tftournament_id), PRIMARY KEY(tfteam_id, tftournament_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tftournament (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, max_participant INT DEFAULT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tfuser_tftournament (tfuser_id INT NOT NULL, tftournament_id INT NOT NULL, INDEX IDX_51176CE167C023A0 (tfuser_id), INDEX IDX_51176CE113499DD2 (tftournament_id), PRIMARY KEY(tfuser_id, tftournament_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tfteam_tftournament ADD CONSTRAINT FK_D95D1D21E9C2289B FOREIGN KEY (tfteam_id) REFERENCES tfteam (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfteam_tftournament ADD CONSTRAINT FK_D95D1D2113499DD2 FOREIGN KEY (tftournament_id) REFERENCES tftournament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfuser_tftournament ADD CONSTRAINT FK_51176CE167C023A0 FOREIGN KEY (tfuser_id) REFERENCES tfuser (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfuser_tftournament ADD CONSTRAINT FK_51176CE113499DD2 FOREIGN KEY (tftournament_id) REFERENCES tftournament (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tfteam_tftournament DROP FOREIGN KEY FK_D95D1D21E9C2289B');
        $this->addSql('ALTER TABLE tfteam_tftournament DROP FOREIGN KEY FK_D95D1D2113499DD2');
        $this->addSql('ALTER TABLE tfuser_tftournament DROP FOREIGN KEY FK_51176CE113499DD2');
        $this->addSql('DROP TABLE tfteam');
        $this->addSql('DROP TABLE tfteam_tftournament');
        $this->addSql('DROP TABLE tftournament');
        $this->addSql('DROP TABLE tfuser_tftournament');
    }
}
