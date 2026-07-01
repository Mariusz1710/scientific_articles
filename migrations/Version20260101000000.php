<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema: users, categories, articles, article versions, reviews, notifications.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE app_user (
                id SERIAL NOT NULL,
                email VARCHAR(180) NOT NULL,
                full_name VARCHAR(191) NOT NULL,
                roles JSON NOT NULL,
                password VARCHAR(255) DEFAULT NULL,
                university_identifier VARCHAR(191) DEFAULT NULL,
                university_name VARCHAR(191) DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX uniq_user_email ON app_user (email)');

        $this->addSql(<<<'SQL'
            CREATE TABLE category (
                id SERIAL NOT NULL,
                name VARCHAR(150) NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE article (
                id SERIAL NOT NULL,
                category_id INT NOT NULL,
                submitter_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                authors VARCHAR(500) NOT NULL,
                abstract TEXT NOT NULL,
                keywords VARCHAR(500) NOT NULL,
                status VARCHAR(255) NOT NULL,
                current_file_path VARCHAR(255) DEFAULT NULL,
                current_file_format VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                submitted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_article_category ON article (category_id)');
        $this->addSql('CREATE INDEX idx_article_submitter ON article (submitter_id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT fk_article_category FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT fk_article_submitter FOREIGN KEY (submitter_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql(<<<'SQL'
            CREATE TABLE article_version (
                id SERIAL NOT NULL,
                article_id INT NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                original_file_name VARCHAR(191) NOT NULL,
                format VARCHAR(255) NOT NULL,
                uploaded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_article_version_article ON article_version (article_id)');
        $this->addSql('ALTER TABLE article_version ADD CONSTRAINT fk_article_version_article FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql(<<<'SQL'
            CREATE TABLE review (
                id SERIAL NOT NULL,
                article_id INT NOT NULL,
                reviewer_id INT NOT NULL,
                decision VARCHAR(255) NOT NULL,
                score INT DEFAULT NULL,
                comments TEXT DEFAULT NULL,
                assigned_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                reviewed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_review_article ON review (article_id)');
        $this->addSql('CREATE INDEX idx_review_reviewer ON review (reviewer_id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT fk_review_article FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT fk_review_reviewer FOREIGN KEY (reviewer_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql(<<<'SQL'
            CREATE TABLE notification (
                id SERIAL NOT NULL,
                recipient_id INT NOT NULL,
                related_article_id INT DEFAULT NULL,
                message VARCHAR(500) NOT NULL,
                is_read BOOLEAN NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_notification_recipient ON notification (recipient_id)');
        $this->addSql('CREATE INDEX idx_notification_article ON notification (related_article_id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_notification_recipient FOREIGN KEY (recipient_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_notification_article FOREIGN KEY (related_article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT fk_notification_recipient');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT fk_notification_article');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT fk_review_article');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT fk_review_reviewer');
        $this->addSql('ALTER TABLE article_version DROP CONSTRAINT fk_article_version_article');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT fk_article_category');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT fk_article_submitter');

        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE article_version');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE app_user');
    }
}
