<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180627164709 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tfmatch (id INT AUTO_INCREMENT NOT NULL, tournament_id INT NOT NULL, next_match_id INT DEFAULT NULL, score LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', comment TINYTEXT DEFAULT NULL, turn INT NOT NULL, INDEX IDX_E79FF51933D1A3E7 (tournament_id), INDEX IDX_E79FF51912A4E038 (next_match_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tfmatch_tfuser (tfmatch_id INT NOT NULL, tfuser_id INT NOT NULL, INDEX IDX_CD001AB87CCDC605 (tfmatch_id), INDEX IDX_CD001AB867C023A0 (tfuser_id), PRIMARY KEY(tfmatch_id, tfuser_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tfmatch_tfteam (tfmatch_id INT NOT NULL, tfteam_id INT NOT NULL, INDEX IDX_84736AEE7CCDC605 (tfmatch_id), INDEX IDX_84736AEEE9C2289B (tfteam_id), PRIMARY KEY(tfmatch_id, tfteam_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tfmatch ADD CONSTRAINT FK_E79FF51933D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tftournament (id)');
        $this->addSql('ALTER TABLE tfmatch ADD CONSTRAINT FK_E79FF51912A4E038 FOREIGN KEY (next_match_id) REFERENCES tfmatch (id)');
        $this->addSql('ALTER TABLE tfmatch_tfuser ADD CONSTRAINT FK_CD001AB87CCDC605 FOREIGN KEY (tfmatch_id) REFERENCES tfmatch (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfmatch_tfuser ADD CONSTRAINT FK_CD001AB867C023A0 FOREIGN KEY (tfuser_id) REFERENCES tfuser (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfmatch_tfteam ADD CONSTRAINT FK_84736AEE7CCDC605 FOREIGN KEY (tfmatch_id) REFERENCES tfmatch (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfmatch_tfteam ADD CONSTRAINT FK_84736AEEE9C2289B FOREIGN KEY (tfteam_id) REFERENCES tfteam (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tfmatch DROP FOREIGN KEY FK_E79FF51912A4E038');
        $this->addSql('ALTER TABLE tfmatch_tfuser DROP FOREIGN KEY FK_CD001AB87CCDC605');
        $this->addSql('ALTER TABLE tfmatch_tfteam DROP FOREIGN KEY FK_84736AEE7CCDC605');
        $this->addSql('DROP TABLE tfmatch');
        $this->addSql('DROP TABLE tfmatch_tfuser');
        $this->addSql('DROP TABLE tfmatch_tfteam');
    }
}
