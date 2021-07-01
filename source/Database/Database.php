<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 19/06/2021 Vagner Cardoso
 */

namespace Core\Database;

use BadMethodCallException;
use Closure;
use Core\Database\Connection\MySqlConnection;
use Core\Database\Connection\PostgreSqlConnection;
use Core\Database\Connection\SQLiteConnection;
use Core\Database\Connection\SqlServerConnection;
use Core\Database\Connection\Statement;
use Core\EventEmitter;
use Core\Support\Obj;
use Exception;
use PDO;

/**
 * Class Database.
 *
 * @method PDO beginTransaction()
 * @method PDO commit()
 * @method PDO errorCode()
 * @method PDO errorInfo()
 * @method PDO exec(string $statement)
 * @method PDO getAttribute(int $attribute)
 * @method PDO getAvailableDrivers()
 * @method PDO inTransaction()
 * @method PDO lastInsertId(string $name = null)
 * @method Statement prepare(string $statement, array $driver_options = array())
 * @method PDO quote(string $string, int $parameter_type = PDO::PARAM_STR)
 * @method PDO rollBack()
 * @method PDO setAttribute(int $attribute, mixed $value)
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Database
{
    /**
     * @var \PDO|null
     */
    protected ?PDO $pdo = null;

    /**
     * @var array
     */
    protected array $connections = [];

    /**
     * @var string
     */
    protected string $defaultDriver = 'mysql';

    /**
     * @param string $method
     * @param mixed  $arguments
     *
     * @return mixed
     */
    public function __call(string $method, mixed $arguments): mixed
    {
        if ($this->pdo instanceof PDO && method_exists($this->pdo, $method)) {
            return $this->pdo->{$method}(...$arguments);
        }

        throw new BadMethodCallException(
            sprintf('Call to undefined method %s::%s()', get_class(), $method)
        );
    }

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
     * @param string|null $driver
     *
     * @throws \Exception
     *
     * @return \Core\Database\Database
     */
    public function driver(?string $driver = null): Database
    {
        return $this->connection($driver);
    }

    /**
     * @param string|null $driver
     *
     * @throws \Exception
     *
     * @return \Core\Database\Database
     */
    public function connection(?string $driver = null): Database
    {
        $driver = $driver ?? $this->getDefaultDriver();

        if (empty($this->connections[$driver])) {
            throw new Exception(
                "Database connections ({$driver}) does not exist configured."
            );
        }

        $connection = $this->connections[$driver];

        if ($connection instanceof PDO) {
            $this->pdo = $connection;

            return $this;
        }

        $connection['driver'] = $connection['driver'] ?? $driver;

        if (!$this->connections[$driver] instanceof PDO) {
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

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->defaultDriver;
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
     * @return \PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * @return array
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    /**
     * @param \Closure $callback
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function transaction(Closure $callback): mixed
    {
        $injectThis = $this;

        if (func_num_args() >= 2) {
            $injectThis = func_get_args()[1];
        }

        $invokeCallback = function () use ($callback, $injectThis) {
            return call_user_func_array($callback, [$injectThis, $this]);
        };

        if ($this->inTransaction()) {
            return $invokeCallback();
        }

        try {
            $this->beginTransaction();
            $result = $invokeCallback();
            $this->commit();

            return $result;
        } catch (Exception $e) {
            $this->rollBack();

            throw $e;
        }
    }

    /**
     * @param string $table
     * @param array  $records
     *
     * @throws \Exception
     *
     * @return int|null
     */
    public function create(string $table, array $records): ?int
    {
        if (!empty($eventRecords = EventEmitter::emit("{$table}:creating", $records))) {
            $records = $eventRecords[0];
        }

        $values = '(:'.implode(', :', array_keys($records)).')';
        $columns = implode(', ', array_keys($records));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES {$values}";
        $lastInsertId = $this->query($sql, $records)->lastInsertId();

        EventEmitter::emit("{$table}:created", $lastInsertId);

        return !empty($lastInsertId)
            ? (int)$lastInsertId
            : null;
    }

    /**
     * @param string $sql
     * @param array  $bindings
     *
     * @throws \Exception
     *
     * @return \Core\Database\Connection\Statement
     */
    public function query(string $sql, array $bindings = []): Statement
    {
        $stmt = $this->prepare($sql);
        $stmt->bindValues($bindings);
        $stmt->execute();

        return $stmt;
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

                array_push($values, '(:'.implode("{$index}, :", array_keys($record))."{$index})");
            } else {
                array_push($values, "('".implode("', '", array_values($record))."')");
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
     * @return object[]|null
     */
    public function update(string $table, array $data, string $conditions, array $bindings = []): ?array
    {
        if (!$rows = $this->findAndTransformRowsObject($table, $conditions, $bindings)) {
            return null;
        }

        if (!empty($eventData = EventEmitter::emit("{$table}:updating", $data, $rows))) {
            $data = $eventData[0];
        }

        $setToArray = [];

        foreach ($data as $key => $value) {
            foreach ($rows as $row) {
                $row->{$key} = $value;
            }

            array_push($setToArray, "{$key} = :{$key}");
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
        $this->query("UPDATE {$table} SET {$setsToString} {$conditions}", $bindings);
        EventEmitter::emit("{$table}:updated", $rows);

        return $rows;
    }

    /**
     * @param string $table
     * @param string $condition
     * @param array  $bindings
     *
     * @throws \Exception
     *
     * @return object[]
     */
    private function findAndTransformRowsObject(string $table, string $condition, array $bindings = []): array
    {
        $rows = $this->read($table, $condition, $bindings)->fetchAll();

        foreach ($rows as $key => $row) {
            $rows[$key] = Obj::fromArray($row);
        }

        return $rows;
    }

    /**
     * @param string      $table
     * @param string|null $condition
     * @param array       $bindings
     *
     * @throws \Exception
     *
     * @return \Core\Database\Connection\Statement
     */
    public function read(string $table, ?string $condition = null, array $bindings = []): Statement
    {
        return $this->query("SELECT {$table}.* FROM {$table} {$condition}", $bindings);
    }

    /**
     * @param string $table
     * @param string $condition
     * @param array  $bindings
     *
     * @throws \Exception
     *
     * @return object[]|null
     */
    public function delete(string $table, string $condition, array $bindings = []): ?array
    {
        if (!$rows = $this->findAndTransformRowsObject($table, $condition, $bindings)) {
            return null;
        }

        EventEmitter::emit("{$table}:deleting", $rows);
        $this->query("DELETE {$table} FROM {$table} {$condition}", $bindings);
        EventEmitter::emit("{$table}:deleted", $rows);

        return $rows;
    }
}
