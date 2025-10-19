<?php
declare(strict_types=1);


namespace DoctrineMigrations;


use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20251018 extends AbstractMigration
{
public function getDescription(): string { return 'Initial schema'; }
public function up(Schema $schema): void
{
$this->addSql('CREATE TABLE members (id VARCHAR(36) PRIMARY KEY, name VARCHAR(255), email VARCHAR(255), status VARCHAR(16), unpaid_cents INT NOT NULL DEFAULT 0)');
$this->addSql('CREATE TABLE books (id VARCHAR(36) PRIMARY KEY, isbn VARCHAR(32), title VARCHAR(255))');
$this->addSql('CREATE TABLE book_authors (book_id VARCHAR(36), author VARCHAR(255))');
$this->addSql('CREATE TABLE book_categories (book_id VARCHAR(36), category VARCHAR(64))');
$this->addSql('CREATE TABLE copies (id VARCHAR(36) PRIMARY KEY, book_id VARCHAR(36), status VARCHAR(16), reserved_for_member_id VARCHAR(36) NULL, INDEX IDX_BOOK (book_id))');
$this->addSql('CREATE TABLE loans (id VARCHAR(36) PRIMARY KEY, member_id VARCHAR(36), book_id VARCHAR(36), copy_id VARCHAR(36), loaned_at DATETIME, due_at DATETIME, returned_at DATETIME NULL, renewal_count INT NOT NULL DEFAULT 0, fine_accrued_cents INT NOT NULL DEFAULT 0, INDEX IDX_LOAN_MEMBER (member_id), INDEX IDX_LOAN_COPY (copy_id))');
$this->addSql('CREATE TABLE reservations (id VARCHAR(36) PRIMARY KEY, book_id VARCHAR(36), member_id VARCHAR(36), position INT NOT NULL, created_at DATETIME, expires_at DATETIME NULL, INDEX IDX_RES_BOOK (book_id))');
$this->addSql('CREATE TABLE fine_payments (id VARCHAR(36) PRIMARY KEY, member_id VARCHAR(36), loan_id VARCHAR(36) NULL, amount_cents INT NOT NULL, paid_at DATETIME)');
$this->addSql('CREATE UNIQUE INDEX uniq_single_reservation_per_member_book ON reservations (book_id, member_id)');
}
public function down(Schema $schema): void { /* drop tables */ }
}
