<?php

namespace BeastBytes\Token\Db\Tests;

use PHPUnit\Framework\ExpectationFailedException;
use Yiisoft\Db\Constraint\Constraint;
use Yiisoft\Db\Constraint\IndexConstraint;

class SchemaTest extends TestCase
{
    use DatabaseTrait;

    public function testSchema(): void
    {
        $this->checkNoTable();
        
        $this->runMigrations();
        $this->checkTable();

        $this->rollbackMigrations();
        $this->checkNoTable();
    }

    private function checkNoTable(): void
    {
        $this->assertNull(
            $this
                ->getDatabase()
                ->getSchema()
                ->getTableSchema(self::TEST_TABLE_NAME)
        );
    }

    private function checkTable(): void
    {
        $database = $this->getDatabase();
        $databaseSchema = $database->getSchema();

        $table = $databaseSchema->getTableSchema(self::TEST_TABLE_NAME);
        $this->assertNotNull($table);

        $columns = $table->getColumns();

        $this->assertArrayHasKey('token', $columns);
        $token = $columns['token'];
        $this->assertSame('string', $token->getType());
        $this->assertSame(127, $token->getSize());
        $this->assertFalse($token->isAllowNull());

        $this->assertArrayHasKey('type', $columns);
        $type = $columns['type'];
        $this->assertSame('string', $type->getType());
        $this->assertSame(63, $type->getSize());
        $this->assertFalse($type->isAllowNull());

        $this->assertArrayHasKey('user_id', $columns);
        $userId = $columns['user_id'];
        $this->assertSame('string', $userId->getType());
        $this->assertSame(255, $userId->getSize());
        $this->assertFalse($userId->isAllowNull());

        $this->assertArrayHasKey('valid_until', $columns);
        $validUntil = $columns['valid_until'];
        $this->assertSame('integer', $validUntil->getType());
        $this->assertFalse($validUntil->isAllowNull());

        $primaryKey = $databaseSchema->getTablePrimaryKey(self::TEST_TABLE_NAME);
        $this->assertInstanceOf(Constraint::class, $primaryKey);
        $this->assertSame(['token'], $primaryKey->getColumnNames());

        $this->assertCount(0, $databaseSchema->getTableForeignKeys(self::TEST_TABLE_NAME));

        $this->assertCount(2, $databaseSchema->getTableIndexes(self::TEST_TABLE_NAME));
        $this->assertIndex(
            table: self::TEST_TABLE_NAME,
            expectedColumnNames: ['token'],
            expectedIsUnique: true,
            expectedIsPrimary: true
        );
        $this->assertIndex(
            table: self::TEST_TABLE_NAME,
            expectedColumnNames: ['type'],
            expectedName: 'idx-yii_token-type',
        );
    }

    protected function assertIndex(
        string $table,
        array $expectedColumnNames,
        ?string $expectedName = null,
        bool $expectedIsUnique = false,
        bool $expectedIsPrimary = false,
    ): void
    {
        /** @var IndexConstraint[] $indexes */
        $indexes = $this
            ->getDatabase()
            ->getSchema()
            ->getTableIndexes($table)
        ;
        $found = false;
        foreach ($indexes as $index) {
            try {
                $this->assertEqualsCanonicalizing($expectedColumnNames, $index->getColumnNames());
            } catch (ExpectationFailedException) {
                continue;
            }

            $found = true;

            $this->assertSame($expectedIsUnique, $index->isUnique());
            $this->assertSame($expectedIsPrimary, $index->isPrimary());

            if ($expectedName !== null) {
                $this->assertSame($expectedName, $index->getName());
            }
        }

        if (!$found) {
            self::fail('Index not found.');
        }
    }
}