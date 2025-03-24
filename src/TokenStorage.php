<?php

declare(strict_types=1);

namespace BeastBytes\Token\Db;

use BeastBytes\Token\CreateTokenTrait;
use BeastBytes\Token\Token;
use BeastBytes\Token\TokenStorageInterface;
use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\IntegrityException;
use Yiisoft\Db\Exception\InvalidArgumentException;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Query\Query;

/**
 * Storage for tokens in a database table. Operations are performed using Yii Database.
 *
 * @psalm-type RawToken = array{
 *     token: string,
 *     type: string,
 *     user_id: string,
 *     valid_until: int
 * }
 */
final class TokenStorage implements TokenStorageInterface
{
    use CreateTokenTrait;

    public const TABLE_NAME = 'yii_token';

    /**
     * @param ConnectionInterface $database Yii database connection instance.
     *
     * @param string $tableName A name of the table for storing tokens.
     * @psalm-param non-empty-string $tableName
     */
    public function __construct(
        private readonly ConnectionInterface $database,
        private readonly string $tableName
    )
    {
    }

    /**
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws InvalidArgumentException
     * @throws NotSupportedException
     * @throws Exception
     */
    public function add(Token $token): bool
    {
        try {
            return $this->database
                ->createCommand()
                ->insert(
                    $this->tableName,
                    $token->toArray(),
                )
                ->execute() > 0
            ;
        } catch (IntegrityException $exception) {
            return false;
        }
    }

    /**
     * @throws Throwable
     */
    public function clear(): void
    {
        $tokenStorage = $this;
        $this
            ->database
            ->transaction(static function (ConnectionInterface $database) use ($tokenStorage): void {
                $database
                    ->createCommand()
                    ->delete($tokenStorage->tableName)
                    ->execute()
                ;
            }
        );
    }

    /**
     * @throws Throwable
     */
    public function delete(Token $token): bool
    {
        $tokenStorage = $this;
        $result = $this
            ->database
            ->transaction(static function (ConnectionInterface $database) use ($tokenStorage, $token): int {
                return $database
                    ->createCommand()
                    ->delete($tokenStorage->tableName, ['token' => $token->getToken()])
                    ->execute()
                ;
            }
        );

        return is_int($result);
    }

    /**
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws Exception
     */
    public function get(string $token): Token|null
    {
        /** @psalm-var RawToken|null $row */
        $row = (new Query($this->database))
            ->from($this->tableName)
            ->where(['token' => $token])
            ->one()
        ;

        return $row === null ? null : $this->createToken($row);
    }

    /**
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws Exception
     */
    public function exists(string $token): bool
    {
        return (new Query($this->database))
            ->from($this->tableName)
            ->where(['token' => $token])
            ->exists()
        ;
    }
}