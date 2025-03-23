<?php

namespace BeastBytes\Token\Db\Tests;

use BeastBytes\Token\CreateTokenTrait;
use BeastBytes\Token\Db\TokenStorage;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionException;

/**
 * @psalm-type RawToken = array{
 *     token: string,
 *     type: string,
 *     user_id: string,
 *     valid_until: int
 * }
 */
class TokenStorageTest extends TestCase
{
    private const TEST_TOKEN_VALUE = 'test-token-value';
    private const TEST_TOKEN_TYPE = 'test-token-type';

    private TokenStorage $tokenStorage;

    use CreateTokenTrait;
    use DatabaseTrait;

    public static function setUpBeforeClass(): void
    {
        (new static(static::class))->runMigrations();
    }

    protected function setUp(): void
    {
        $this->tokenStorage = new TokenStorage(
            $this->getDatabase(),
            self::TEST_TABLE_NAME
        );
    }

    protected function tearDown(): void
    {
        $this->tokenStorage->clear();
    }

    /**
     * @throws ReflectionException
     */
    #[dataProvider('tokenProvider')]
    public function testAddAndGetToken(array $rawToken): void
    {
        $token = $this->createToken($rawToken);

        $this->assertFalse($this
            ->tokenStorage
            ->exists($rawToken['token'])
        );

        $this->assertTrue($this
            ->tokenStorage
            ->add($token)
        );

        $this->assertTrue($this
            ->tokenStorage
            ->exists($rawToken['token'])
        );

        $retrievedToken = $this
            ->tokenStorage
            ->get($rawToken['token'])
        ;

        $this->assertEquals($token, $retrievedToken);
    }

    /**
     * @throws ReflectionException
     */
    #[dataProvider('tokenProvider')]
    public function testCantAddDuplicateToken(array $rawToken): void
    {
        $token = $this->createToken($rawToken);

        $this->assertFalse($this
            ->tokenStorage
            ->exists($rawToken['token'])
        );

        $this->assertTrue($this
            ->tokenStorage
            ->add($token)
        );

        $this->assertTrue($this
            ->tokenStorage
            ->exists($rawToken['token'])
        );

        $this->assertFalse($this
            ->tokenStorage
            ->add($token)
        );
    }

    public function testGetTokenReturnsNullWhenNoTokenExists(): void
    {
        $retrievedToken = $this
            ->tokenStorage
            ->get(self::TEST_TOKEN_VALUE)
        ;

        $this->assertNull($retrievedToken);
    }

    /**
     * @throws ReflectionException
     */
    #[dataProvider('tokenProvider')]
    public function testDeleteToken(array $rawToken): void
    {
        $token = $this->createToken($rawToken);

        $this
            ->tokenStorage
            ->add($token)
        ;

        $this->assertTrue($this
            ->tokenStorage
            ->exists($rawToken['token'])
        );

        $this
            ->tokenStorage
            ->delete($token)
        ;

        $this->assertFalse($this
            ->tokenStorage
            ->exists($rawToken['token'])
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testDeleteTokenWhenNoTokenExists(): void
    {
        $this
            ->tokenStorage
            ->clear()
        ;

        $token = $this->createToken(self::getRawToken(random_int(0, 100)));

        $this->assertTrue($this
            ->tokenStorage
            ->delete($token)
        );
    }

    public function testClearTokens(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $token = $this->createToken(self::getRawToken(random_int(0, 100)));

            $this
                ->tokenStorage
                ->add($token)
            ;

            $this->assertTrue($this
                ->tokenStorage
                ->exists($token->getToken())
            );
        }

        $this
            ->tokenStorage
            ->clear()
        ;

        for ($i = 0; $i < 10; $i++) {
            $token = self::TEST_TOKEN_VALUE . '-' . $i;

            $this->assertFalse($this
                ->tokenStorage
                ->exists($token)
            );
        }
    }

    public static function tokenProvider(): Generator
    {
        for ($i = 0; $i < 10; $i++) {
            yield 'test-token-value-' . $i => [self::getRawToken($i)];
        }
    }

    private static function getRawToken(int $i): array
    {
        return [
            'token' => self::TEST_TOKEN_VALUE . '-' . $i,
            'type' => self::TEST_TOKEN_TYPE,
            'user_id' => random_int(1, 100),
            'valid_until' => time() + random_int(-100, 100) * 3600
        ];
    }
}