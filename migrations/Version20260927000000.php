<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260927000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove university CAS login support: drop university_identifier and university_name columns from app_user.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_user DROP COLUMN IF EXISTS university_identifier');
        $this->addSql('ALTER TABLE app_user DROP COLUMN IF EXISTS university_name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_user ADD COLUMN university_identifier VARCHAR(191) DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user ADD COLUMN university_name VARCHAR(191) DEFAULT NULL');
    }
}
