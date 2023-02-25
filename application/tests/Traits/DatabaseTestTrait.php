<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/02/2023 Vagner Cardoso
 */

namespace Tests\Traits;

use Core\Support\Path;
use PDO;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Tests\Fixture\Fixture;

/**
 * Trait DatabaseTestTrait.
 */
trait DatabaseTestTrait
{
    /**
     * Path to schema.sql.
     *
     * @var string
     */
    protected string $schemaFile = '';

    /**
     * @var bool
     */
    protected bool $runMigration = false;

    /**
     * @var Fixture[]
     */
    protected array $fixtures = [];

    /**
     * Create tables and insert fixtures.
     *
     * TestCases must call this method inside setUp().
     *
     * @param string $schemaFile   The sql schema file
     * @param bool   $runMigration
     */
    protected function setUpDatabase(string $schemaFile, bool $runMigration = false): void
    {
        $this->schemaFile = $schemaFile;
        $this->runMigration = $runMigration;

        $this->createTables();
        $this->truncateTables();
        $this->insertFixtures();
    }

    /**
     * @return void
     */
    protected function createTables(): void
    {
        if (defined('DB_TEST_TRAIT_INIT')) {
            return;
        }

        if ($this->runMigration) {
            $this->runMigration();
        }

        if (!$this->runMigration && !empty($this->schemaFile)) {
            $this->unsetStatsExpiry();
            $this->dropTables();
            $this->importSchema();
        }

        define('DB_TEST_TRAIT_INIT', true);
    }

    /**
     * Run migrate.
     *
     * @return void
     */
    protected function runMigration(): void
    {
        $phinxApplication = new PhinxApplication();
        $phinxApplication->doRun(new ArgvInput([
            'phinx',
            'migrate',
            '-c',
            Path::config('phinx.php'),
        ]), new ConsoleOutput());
    }

    /**
     * Workaround for MySQL 8: update_time not working.
     *
     * https://bugs.mysql.com/bug.php?id=95407
     *
     * @return void
     */
    private function unsetStatsExpiry(): void
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
            throw new \UnexpectedValueException('Invalid SQL statement');
        }

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        if (false === $row) {
            return null;
        }

        return (string)$row['Value'];
    }

    /**
     * Get database connection.
     *
     * @return \PDO The PDO instance
     */
    protected function getConnection(): \PDO
    {
        return $this->container->get(\PDO::class);
    }

    /**
     * Clean up database. Truncate tables.
     *
     * @return void
     */
    protected function dropTables(): void
    {
        $this->disableForeignAndUniqueCheck(function (\PDO $pdo) {
            $sql = [];

            foreach ($this->getSchemaTables() as $row) {
                $sql[] = sprintf('DROP TABLE %s CASCADE;', $row['tableName']);
            }

            if ($sql) {
                $pdo->exec(implode("\n", $sql));
            }
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
        // $pdo->exec('SET unique_checks=0; SET foreign_key_checks=0;');
        call_user_func($callable, $pdo);
        // $pdo->exec('SET unique_checks=1; SET foreign_key_checks=1;');
    }

    /**
     * @return array
     */
    protected function getSchemaTables(): array
    {
        $query = 'SELECT TABLE_NAME AS "tableName" FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE();';

        if (true) {
            $query = 'SELECT table_name AS "tableName" FROM information_schema.tables WHERE table_catalog = CURRENT_DATABASE() AND table_schema = \'public\';';
        }

        $statement = $this->createQueryStatement($query);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
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
    protected function createQueryStatement(string $sql): \PDOStatement
    {
        $statement = $this->getConnection()->query($sql, \PDO::FETCH_ASSOC);

        if (!$statement instanceof \PDOStatement) {
            throw new \UnexpectedValueException('Invalid SQL statement');
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

        $existSchemaFile = file_exists($this->schemaFile);

        if (!$existSchemaFile && $this->runMigration) {
            return;
        }

        if (!$this->runMigration && !file_exists($this->schemaFile)) {
            throw new \UnexpectedValueException(sprintf('File not found: %s', $this->schemaFile));
        }

        $this->disableForeignAndUniqueCheck(function (\PDO $pdo) {
            $pdo->exec((string)file_get_contents($this->schemaFile));
        });
    }

    /**
     * Clean up database.
     *
     * @return void
     */
    protected function truncateTables(): void
    {
        $this->disableForeignAndUniqueCheck(function (\PDO $pdo) {
            $sql = [];

            foreach ($this->getSchemaTables() as $row) {
                if ($this->runMigration && 'migrations' === $row['tableName']) {
                    continue;
                }
                $sql[] = sprintf('TRUNCATE TABLE %s CASCADE;', $row['tableName']);
            }

            if (count($sql) > 0) {
                $pdo->exec(implode("\n", $sql));
            }
        });
    }

    /**
     * Iterate over all fixtures and insert them into their tables.
     *
     * @param array<string> $fixtures The class name fixtures
     *
     * @return void
     */
    protected function insertFixtures(array $fixtures = []): void
    {
        if (empty($fixtures)) {
            $fixtures = $this->fixtures;
        }

        if (empty($fixtures)) {
            return;
        }

        foreach ($fixtures as $fixture) {
            $object = new $fixture();

            foreach ($object->getRecords() as $record) {
                $this->insertFixture($object->getTable(), $record);
            }
        }
    }

    /**
     * Insert row into table.
     *
     * @param string $table The table name
     * @param array  $row   The row data
     *
     * @return void
     */
    protected function insertFixture(string $table, array $row): void
    {
        $columns = implode(', ', array_keys($row));
        $values = '(:'.implode(', :', array_keys($row)).')';

        $sql = sprintf('INSERT INTO %s (%s) VALUES %s', $table, $columns, $values);
        $statement = $this->createPreparedStatement($sql);

        foreach ($row as $column => $value) {
            $statement->bindValue(":{$column}", $value);
        }

        $statement->execute();
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
    protected function createPreparedStatement(string $sql): \PDOStatement
    {
        $statement = $this->getConnection()->prepare($sql);

        if (!$statement instanceof \PDOStatement) {
            throw new \UnexpectedValueException('Invalid SQL statement');
        }

        return $statement;
    }

    /**
     * This method is called after each test.
     */
    protected function tearDownDatabase(): void
    {
        // $this->unsetStatsExpiry();

        if ($this->runMigration) {
            $this->phinxRollback();
        }

        if (!empty($this->schemaFile) && !$this->runMigration) {
            $this->dropTables();
        }
    }

    /**
     * Run rollback.
     *
     * @return void
     */
    protected function phinxRollback(): void
    {
        $phinxApplication = new PhinxApplication();
        $phinxApplication->doRun(new ArgvInput([
            'phinx',
            'rollback',
            '-c',
            Path::config('phinx.php'),
            '-t',
            '0',
        ]), new ConsoleOutput());
    }

    /**
     * Asserts that a given table is the same as the given row.
     *
     * @param array      $expectedRow Row expected to find
     * @param string     $table       Table to look into
     * @param int        $id          The primary key
     * @param array|null $fields      The columns
     * @param string     $message     Optional message
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
     * @param string     $table  Table name
     * @param int        $id     The primary key value
     * @param array|null $fields The array of fields
     *
     * @throws \DomainException
     *
     * @return array Row
     */
    protected function getTableRowById(string $table, int $id, array $fields = null): array
    {
        $sql = sprintf('SELECT * FROM %s WHERE id = :id', $table);
        $statement = $this->createPreparedStatement($sql);
        $statement->execute(['id' => $id]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        if (empty($row)) {
            throw new \DomainException(sprintf('Row not found: %s', $id));
        }

        if ($fields) {
            $row = array_intersect_key($row, array_flip($fields));
        }

        return $row;
    }

    /**
     * Asserts that a given table equals the given row.
     *
     * @param array      $expectedRow Row expected to find
     * @param string     $table       Table to look into
     * @param int        $id          The primary key
     * @param array|null $fields      The columns
     * @param string     $message     Optional message
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
        $sql = sprintf('SELECT COUNT(*) AS counter FROM %s;', $table);
        $statement = $this->createQueryStatement($sql);
        $row = $statement->fetch(\PDO::FETCH_ASSOC) ?: [];

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
     * @return array Row
     */
    protected function findTableRowById(string $table, int $id): array
    {
        $sql = sprintf('SELECT * FROM %s WHERE id = :id', $table);
        $statement = $this->createPreparedStatement($sql);
        $statement->execute(['id' => $id]);

        return $statement->fetch(\PDO::FETCH_ASSOC) ?: [];
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
