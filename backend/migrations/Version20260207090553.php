<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Link;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207090553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблицы для Link';
    }

    public function up(Schema $schema): void
    {
        $links = $schema->createTable('links');
        $links->addColumn('id', 'integer')
            ->setAutoIncrement(true);

        $links->addColumn('urlTarget', 'string')
            ->setNotnull(true)
            ->setLength(Link::URL_TARGET_LENGTH);

        $links->addColumn('shortCode', 'string')
            ->setNotnull(true)
            ->setLength(Link::URL_SHORT_CODE_LENGTH);

        $links->addPrimaryKeyConstraint(new PrimaryKeyConstraint(
            name: UnqualifiedName::quoted('PK_Links'),
            columnNames: [UnqualifiedName::quoted('id')],
            isClustered: true
        ));
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('links');
    }
}
