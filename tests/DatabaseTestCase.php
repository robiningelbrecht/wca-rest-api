<?php

namespace App\Tests;

use App\Infrastructure\Environment\Settings;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;

abstract class DatabaseTestCase extends ContainerTestCase
{
    private static bool $testDatabaseCreated = false;
    protected static ?Connection $connection = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$connection) {
            self::$connection = $this->getContainer()->get(Connection::class);
        }

        if (!self::$testDatabaseCreated) {
            $this->createTestDatabase();
            self::$testDatabaseCreated = true;
        }

        $this->getConnection()->beginTransaction();
    }

    public function tearDown(): void
    {
        try {
            $this->getConnection()->rollBack();
        } catch (ConnectionException) {
        } catch (\PDOException) {
        }
    }

    public function getConnection(): Connection
    {
        return self::$connection;
    }

    private function createTestDatabase(): void
    {
        /** @var Connection $connection */
        $connection = $this->getContainer()->get(Connection::class);

        $connection->executeStatement(
            file_get_contents(Settings::getAppRoot().'/tests/create-database.sql')
        );
    }
}
