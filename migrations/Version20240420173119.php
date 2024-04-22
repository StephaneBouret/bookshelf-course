<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240420173119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE borrowing_book (borrowing_id INT NOT NULL, book_id INT NOT NULL, INDEX IDX_1EA4AF6D4675F064 (borrowing_id), INDEX IDX_1EA4AF6D16A2B381 (book_id), PRIMARY KEY(borrowing_id, book_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE borrowing_book ADD CONSTRAINT FK_1EA4AF6D4675F064 FOREIGN KEY (borrowing_id) REFERENCES borrowing (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE borrowing_book ADD CONSTRAINT FK_1EA4AF6D16A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE borrowing_book DROP FOREIGN KEY FK_1EA4AF6D4675F064');
        $this->addSql('ALTER TABLE borrowing_book DROP FOREIGN KEY FK_1EA4AF6D16A2B381');
        $this->addSql('DROP TABLE borrowing_book');
    }
}
