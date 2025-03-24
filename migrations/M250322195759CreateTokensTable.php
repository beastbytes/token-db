<?php

declare(strict_types=1);

use BeastBytes\Token\Db\TokenStorage;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

final class M250322195759CreateTokensTable implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function up(MigrationBuilder $b): void
    {
        $b->createTable(
            TokenStorage::TABLE_NAME,
            [
                'token' => 'string(127) NOT NULL PRIMARY KEY',
                'type' => 'string(63) NOT NULL',
                'user_id' => 'string(255) NOT NULL',
                'valid_until' => 'integer NOT NULL',
            ],
        );
        $b->createIndex(TokenStorage::TABLE_NAME, 'idx-' . TokenStorage::TABLE_NAME . '-type', 'type');
    }

    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function down(MigrationBuilder $b): void
    {
        $b->dropTable(TokenStorage::TABLE_NAME);
    }
}