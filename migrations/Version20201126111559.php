<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201126111559 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
           CREATE TABLE user_tracking (
                id VARCHAR(36),
                recorder ENUM('frame', 'js_framedata'),
                url TEXT,
                recorded_at DATETIME(2) # uses fractions up to 2 positions
           )
        SQL);
    }
}
