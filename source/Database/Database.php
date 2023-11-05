<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/11/2023 Vagner Cardoso
 */

namespace Core\Database;

use Core\Database\Connection\MySqlConnection;
use Core\Database\Connection\PostgreSqlConnection;
use Core\Database\Connection\SQLiteConnection;
use Core\Database\Connection\SqlServerConnection;
use Core\Database\Connection\Statement;
use Core\EventEmitter;

/**
 * Class Database.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Database
{
    /**
     * @var \PDO|null
     */
    protected null|\PDO $pdo = null;

    /**
     * @var array
     */
    protected array $connections = [];

    /**
     * @var string
     */
    protected string $currentDriver = 'pgsql';

    /**
     * @var string
     */
    protected string $defaultDriver = 'pgsql';

    /**
     * @param string $driver
     * @param array  $connection
     *
     * @return $this
     */
    public function addConnection(string $driver, array $connection): Database
    {
        $this->connections[$driver] = $connection;

        return $this;
    }

    /**
     * @param string $defaultDriver
     *
     * @return \Core\Database\Database
     */
    public function setDefaultDriver(string $defaultDriver): Database
    {
        $this->defaultDriver = $defaultDriver;

        return $this;
    }

    /**
     * @param string|null $driver
     *
     * @throws \Exception
     *
     * @return \Core\Database\Database
     */
    public function connection(string $driver = null): Database
    {
        $driver = $driver ?? $this->defaultDriver;

        if (empty($this->connections[$driver])) {
            throw new \Exception(
                "Database connections ({$driver}) does not exist configured."
            );
        }

        $connection = $this->connections[$driver];

        if ($connection instanceof \PDO) {
            $this->pdo = $connection;

            return $this;
        }

        $connection['driver'] = $connection['driver'] ?? $driver;

        if (!$this->connections[$driver] instanceof \PDO) {
            if ('pgsql' == $connection['driver']) {
                $this->connections[$driver] = (new PostgreSqlConnection($connection));
            } elseif ('sqlsrv' == $connection['driver']) {
                $this->connections[$driver] = (new SqlServerConnection($connection));
            } elseif ('sqlite' == $connection['driver']) {
                $this->connections[$driver] = (new SQLiteConnection($connection));
            } else {
                $this->connections[$driver] = (new MySqlConnection($connection));
            }
        }

        $this->pdo = $this->connections[$driver];
        $this->currentDriver = $driver;

        return $this;
    }

    /**
     * @param \Closure $callback
     *
     * @throws \Throwable
     *
     * @return mixed
     */
    public function transaction(\Closure $callback): mixed
    {
        $injectThis = $this;

        if (func_num_args() >= 2) {
            $injectThis = func_get_args()[1];
        }

        $invokeCallback = function () use ($callback, $injectThis) {
            return call_user_func_array($callback, [$injectThis, $this]);
        };

        if ($this->getPdo()->inTransaction()) {
            return $invokeCallback();
        }

        try {
            $this->getPdo()->beginTransaction();
            $result = $invokeCallback();
            $this->getPdo()->commit();

            return $result;
        } catch (\Throwable $e) {
            $this->getPdo()->rollBack();

            throw $e;
        }
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        if (!$this->pdo instanceof \PDO) {
            throw new \DomainException('Database connection is not established.');
        }

        return $this->pdo;
    }

    /**
     * @param string $table
     * @param array  $records
     *
     * @throws \Exception
     *
     * @return array|int|string|null
     */
    public function create(string $table, array $records): null|array|int|string
    {
        if (!empty($records[0]) && is_array($records[0])) {
            throw new \InvalidArgumentException('Use method (createMultiple).');
        }

        if (!empty($eventRecords = EventEmitter::emit("{$table}:creating", $records))) {
            $records = $eventRecords[0];
        }

        $values = '(:'.implode(', :', array_keys($records)).')';
        $columns = implode(', ', array_keys($records));
        $returning = 'pgsql' === $this->currentDriver ? 'RETURNING *' : '';

        $sql = trim("INSERT INTO {$table} ({$columns}) VALUES {$values} {$returning};");
        $statement = $this->query($sql, $records);

        if ('pgsql' === $this->currentDriver) {
            $result = $statement->fetch();
        } else {
            $result = $statement->lastInsertId();
        }

        EventEmitter::emit("{$table}:created", $result);

        return $result;
    }

    /**
     * @param string $sql
     * @param array  $bindings
     *
     * @return Statement
     */
    public function query(string $sql, array $bindings = []): Statement
    {
        $statement = $this->getPdo()->prepare($sql);

        if (false === $statement) {
            throw new \DomainException('Prepare statement returns false.');
        }

        $statement->addBindings($bindings);
        $statement->execute();

        return $statement;
    }

    /**
     * @param string $table
     * @param array  $records
     * @param bool   $bindValues
     *
     * @throws \Exception
     */
    public function createMultiple(string $table, array $records, bool $bindValues = true): void
    {
        $values = [];
        $bindings = [];
        $columnsToArray = array_keys($records[0]);

        foreach ($records as $index => $record) {
            if ($bindValues) {
                foreach ($columnsToArray as $column) {
                    $bindings["{$column}{$index}"] = $record[$column];
                }

                $values[] = '(:'.implode("{$index}, :", array_keys($record))."{$index})";
            } else {
                $values[] = "('".implode("', '", array_values($record))."')";
            }
        }

        $columnsToString = trim(implode(', ', $columnsToArray));
        $valuesToString = trim(implode(', ', $values));

        $this->query("INSERT INTO {$table} ({$columnsToString}) VALUES {$valuesToString}", $bindings);
    }

    /**
     * @param string $table
     * @param array  $data
     * @param string $conditions
     * @param array  $bindings
     *
     * @throws \Exception
     *
     * @return array<int, array<string, mixed>>|null
     */
    public function update(string $table, array $data, string $conditions, array $bindings = []): ?array
    {
        if (!$rows = $this->findInternalRows($table, $conditions, $bindings)) {
            return null;
        }

        if (!empty($eventData = EventEmitter::emit("{$table}:updating", $data, $rows))) {
            $data = $eventData[0];
        }

        $setToArray = [];

        foreach ($data as $key => $value) {
            foreach ($rows as $row) {
                $row[$key] = $value;
            }

            $setToArray[] = "{$key} = :{$key}";
            $intersectEqualBinding = array_intersect_key($data, $bindings);

            if (!empty($intersectEqualBinding[$key])) {
                $newKey = sprintf('%s_%s', $key, bin2hex(random_bytes(3)));
                $conditions = str_replace(":{$key}", ":{$newKey}", $conditions);

                $bindings[$newKey] = $value;
            } else {
                $bindings[$key] = $value;
            }
        }

        $setsToString = implode(', ', $setToArray);
        $this->query("UPDATE {$table} SET {$setsToString} WHERE {$conditions};", $bindings);
        EventEmitter::emit("{$table}:updated", $rows);

        return $rows;
    }

    /**
     * @param string $table
     * @param string $condition
     * @param array  $bindings
     *
     * @return array<int, array<string, mixed>>
     */
    private function findInternalRows(string $table, string $condition, array $bindings = []): array
    {
        return $this->query(
            "SELECT {$table}.* FROM {$table} WHERE 1 = 1 AND {$condition}",
            $bindings
        )
            ->fetchAll(\PDO::FETCH_ASSOC)
        ;
    }

    /**
     * @param string $table
     * @param string $condition
     * @param array  $bindings
     *
     * @return array<int, array<string, mixed>>|null
     */
    public function delete(string $table, string $condition, array $bindings = []): ?array
    {
        if (!$rows = $this->findInternalRows($table, $condition, $bindings)) {
            return null;
        }

        EventEmitter::emit("{$table}:deleting", $rows);
        $this->query("DELETE FROM {$table} WHERE {$condition};", $bindings);
        EventEmitter::emit("{$table}:deleted", $rows);

        return $rows;
    }
}
