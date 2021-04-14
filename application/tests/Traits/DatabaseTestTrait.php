<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 14/04/2021 Vagner Cardoso
 */

namespace Tests\Traits;

use DomainException;
use InvalidArgumentException;
use PDO;
use PDOStatement;
use Tests\Fixture\FixtureInterface;
use UnexpectedValueException;

/**
 * Trait DatabaseTestTrait.
 */
trait DatabaseTestTrait
{
    /**
     * Path to schema.sql.
     *
     * @var string|null
     */
    protected ?string $schemaFile = null;

    /**
     * @var bool
     */
    protected bool $runPhinx = false;

    /**
     * @var FixtureInterface[]
     */
    protected array $fixtures = [];

    /**
     * Create tables and insert fixtures.
     *
     * TestCases must call this method inside setUp().
     *
     * @param string|null $schemaFile The sql schema file
     * @param bool        $runPhinx
     */
    protected function setUpDatabase(string $schemaFile = null, bool $runPhinx = false)
    {
        $this->schemaFile = $schemaFile;
        $this->runPhinx = $runPhinx;

        $this->getConnection();

        if ($runPhinx && empty($schemaFile)) {
            $this->phinxMigrate();
        }

        if (!empty($schemaFile)) {
            $this->unsetStatsExpiry();
            $this->importSchema();
        }

        if (!empty($this->fixtures)) {
            $this->insertFixtures($this->fixtures);
        }
    }

    /**
     * This method is called after each test.
     */
    protected function tearDownDatabase(): void
    {
        $this->unsetStatsExpiry();
        $this->dropTables();
    }

    /**
     * Run rollback.
     *
     * @return void
     */
    protected function phinxRollback(): void
    {
        shell_exec('./phinx rollback -t 0');
    }

    /**
     * Run migrate.
     *
     * @return void
     */
    protected function phinxMigrate(): void
    {
        shell_exec('./phinx migrate');
    }

    /**
     * Get database connection.
     *
     * @return \PDO The PDO instance
     */
    protected function getConnection(): PDO
    {
        return $this->container->get(PDO::class);
    }

    /**
     * Workaround for MySQL 8: update_time not working.
     *
     * https://bugs.mysql.com/bug.php?id=95407
     *
     * @return void
     */
    private function unsetStatsExpiry()
    {
        $expiry = $this->getDatabaseVariable('information_schema_stats_expiry');
        $version = (string)$this->getDatabaseVariable('version');

        if (null !== $expiry && version_compare($version, '8.0.0', '>=')) {
            $this->getConnection()->exec('SET information_schema_stats_expiry=0;');
        }
    }

    /**
     * Get database variable.
     *
     * @param string $variable The variable
     *
     * @return string|null The value
     */
    protected function getDatabaseVariable(string $variable): ?string
    {
        $statement = $this->getConnection()->prepare("SHOW VARIABLES LIKE '{$variable}'");

        if (!$statement || false === $statement->execute()) {
            throw new UnexpectedValueException('Invalid SQL statement');
        }

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (false === $row) {
            return null;
        }

        return (string)$row['Value'];
    }

    /**
     * @return array
     */
    protected function getSchemaTables(): array
    {
        $statement = $this->createQueryStatement('
            SELECT TABLE_NAME AS tableName
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
        ');

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Clean up database. Truncate tables.
     *
     * @return void
     */
    protected function dropTables(): void
    {
        $this->disableForeignAndUniqueCheck(function (PDO $pdo) {
            $sql = [];

            foreach ($this->getSchemaTables() as $row) {
                $sql[] = sprintf('DROP TABLE `%s`;', $row['tableName']);
            }

            if ($sql) {
                $pdo->exec(implode("\n", $sql));
            }
        });
    }

    /**
     * Create PDO statement.
     *
     * @param string $sql The sql
     *
     * @throws \UnexpectedValueException
     *
     * @return \PDOStatement The statement
     */
    private function createQueryStatement(string $sql): PDOStatement
    {
        $statement = $this->getConnection()->query($sql, PDO::FETCH_ASSOC);

        if (!$statement instanceof PDOStatement) {
            throw new UnexpectedValueException('Invalid SQL statement');
        }

        return $statement;
    }

    /**
     * Import table schema.
     *
     * @throws \UnexpectedValueException
     *
     * @return void
     */
    protected function importSchema(): void
    {
        if (empty($this->schemaFile)) {
            return;
        }

        if (!file_exists($this->schemaFile)) {
            throw new UnexpectedValueException(sprintf('File not found: %s', $this->schemaFile));
        }

        $this->disableForeignAndUniqueCheck(function (PDO $pdo) {
            $pdo->exec((string)file_get_contents($this->schemaFile));
        });
    }

    /**
     * Disable checking ethnic types and foreign keys.
     *
     * @param \Closure $callable
     *
     * @return void
     */
    protected function disableForeignAndUniqueCheck(\Closure $callable): void
    {
        $pdo = $this->getConnection();
        $pdo->exec('SET unique_checks=0; SET foreign_key_checks=0;');
        call_user_func($callable, $pdo);
        $pdo->exec('SET unique_checks=1; SET foreign_key_checks=1;');
    }

    /**
     * Clean up database.
     *
     * @return void
     */
    protected function truncateTables(): void
    {
        $this->disableForeignAndUniqueCheck(function (PDO $pdo) {
            $sql = [];

            foreach ($this->getSchemaTables() as $row) {
                $sql[] = sprintf('TRUNCATE TABLE `%s`;', $row['tableName']);
            }

            if ($sql) {
                $pdo->exec(implode("\n", $sql));
            }
        });
    }

    /**
     * Iterate over all fixtures and insert them into their tables.
     *
     * @param array<mixed> $fixtures The fixtures
     *
     * @return void
     */
    protected function insertFixtures(array $fixtures): void
    {
        foreach ($fixtures as $fixture) {
            if (!is_a($fixture, FixtureInterface::class)) {
                throw new InvalidArgumentException(
                    "Fixture {$fixture} must be an instance of: Tests\\Fixture\\FixtureInterface."
                );
            }

            $object = new $fixture();

            foreach ($object->getRecords() as $row) {
                $this->insertFixture($object->getTable(), $row);
            }
        }
    }

    /**
     * Insert row into table.
     *
     * @param string       $table The table name
     * @param array<mixed> $row   The row data
     *
     * @return void
     */
    protected function insertFixture(string $table, array $row): void
    {
        $fields = array_keys($row);

        array_walk(
            $fields,
            function (&$value) {
                $value = sprintf('`%s`=:%s', $value, $value);
            }
        );

        $sql = sprintf('INSERT INTO `%s` SET %s', $table, implode(',', $fields));
        $statement = $this->createPreparedStatement($sql);
        $statement->execute($row);
    }

    /**
     * Create PDO statement.
     *
     * @param string $sql The sql
     *
     * @throws \UnexpectedValueException
     *
     * @return \PDOStatement The statement
     */
    private function createPreparedStatement(string $sql): PDOStatement
    {
        $statement = $this->getConnection()->prepare($sql);

        if (!$statement instanceof PDOStatement) {
            throw new UnexpectedValueException('Invalid SQL statement');
        }

        return $statement;
    }

    /**
     * Asserts that a given table is the same as the given row.
     *
     * @param array<mixed>      $expectedRow Row expected to find
     * @param string            $table       Table to look into
     * @param int               $id          The primary key
     * @param array<mixed>|null $fields      The columns
     * @param string            $message     Optional message
     *
     * @return void
     */
    protected function assertTableRow(
        array $expectedRow,
        string $table,
        int $id,
        array $fields = null,
        string $message = ''
    ): void {
        $this->assertSame(
            $expectedRow,
            $this->getTableRowById($table, $id, $fields ?: array_keys($expectedRow)),
            $message
        );
    }

    /**
     * Fetch row by ID.
     *
     * @param string            $table  Table name
     * @param int               $id     The primary key value
     * @param array<mixed>|null $fields The array of fields
     *
     * @throws \DomainException
     *
     * @return array<mixed> Row
     */
    protected function getTableRowById(string $table, int $id, array $fields = null): array
    {
        $sql = sprintf('SELECT * FROM `%s` WHERE `id` = :id', $table);
        $statement = $this->createPreparedStatement($sql);
        $statement->execute(['id' => $id]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (empty($row)) {
            throw new DomainException(sprintf('Row not found: %s', $id));
        }

        if ($fields) {
            $row = array_intersect_key($row, array_flip($fields));
        }

        return $row;
    }

    /**
     * Asserts that a given table equals the given row.
     *
     * @param array<mixed>      $expectedRow Row expected to find
     * @param string            $table       Table to look into
     * @param int               $id          The primary key
     * @param array<mixed>|null $fields      The columns
     * @param string            $message     Optional message
     *
     * @return void
     */
    protected function assertTableRowEquals(
        array $expectedRow,
        string $table,
        int $id,
        array $fields = null,
        string $message = ''
    ): void {
        $this->assertEquals(
            $expectedRow,
            $this->getTableRowById($table, $id, $fields ?: array_keys($expectedRow)),
            $message
        );
    }

    /**
     * Asserts that a given table contains a given row value.
     *
     * @param mixed  $expected The expected value
     * @param string $table    Table to look into
     * @param int    $id       The primary key
     * @param string $field    The column name
     * @param string $message  Optional message
     *
     * @return void
     */
    protected function assertTableRowValue(
        mixed $expected,
        string $table,
        int $id,
        string $field,
        string $message = ''
    ): void {
        $actual = $this->getTableRowById($table, $id, [$field])[$field];
        $this->assertSame($expected, $actual, $message);
    }

    /**
     * Asserts that a given table contains a given number of rows.
     *
     * @param int    $expected The number of expected rows
     * @param string $table    Table to look into
     * @param string $message  Optional message
     *
     * @return void
     */
    protected function assertTableRowCount(int $expected, string $table, string $message = ''): void
    {
        $this->assertSame($expected, $this->getTableRowCount($table), $message);
    }

    /**
     * Get table row count.
     *
     * @param string $table The table name
     *
     * @return int The number of rows
     */
    protected function getTableRowCount(string $table): int
    {
        $sql = sprintf('SELECT COUNT(*) AS counter FROM `%s`;', $table);
        $statement = $this->createQueryStatement($sql);
        $row = $statement->fetch(PDO::FETCH_ASSOC) ?: [];

        return (int)($row['counter'] ?? 0);
    }

    /**
     * Asserts that a given table contains a given number of rows.
     *
     * @param string $table   Table to look into
     * @param int    $id      The id
     * @param string $message Optional message
     *
     * @return void
     */
    protected function assertTableRowExists(string $table, int $id, string $message = ''): void
    {
        $this->assertTrue((bool)$this->findTableRowById($table, $id), $message);
    }

    /**
     * Fetch row by ID.
     *
     * @param string $table Table name
     * @param int    $id    The primary key value
     *
     * @return array<mixed> Row
     */
    protected function findTableRowById(string $table, int $id): array
    {
        $sql = sprintf('SELECT * FROM `%s` WHERE `id` = :id', $table);
        $statement = $this->createPreparedStatement($sql);
        $statement->execute(['id' => $id]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Asserts that a given table contains a given number of rows.
     *
     * @param string $table   Table to look into
     * @param int    $id      The id
     * @param string $message Optional message
     *
     * @return void
     */
    protected function assertTableRowNotExists(string $table, int $id, string $message = ''): void
    {
        $this->assertFalse((bool)$this->findTableRowById($table, $id), $message);
    }
}
