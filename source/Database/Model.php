<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/07/2021 Vagner Cardoso
 */

namespace Core\Database;

use ArrayAccess;
use Closure;
use Core\Config;
use Core\Database\Connection\Statement;
use Core\Support\Common;
use Core\Support\Obj;
use InvalidArgumentException;
use JsonSerializable;
use PDO;
use ReflectionClass;
use stdClass;

/**
 * Class Model.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Model implements ArrayAccess, JsonSerializable
{
    /**
     * @var \Core\Database\Database|null
     */
    protected static ?Database $database = null;

    /**
     * @var string|null
     */
    protected ?string $driver = null;

    /**
     * @var string
     */
    protected string $table;

    /**
     * @var string|null
     */
    protected ?string $primaryKey = 'id';

    /**
     * @var string|null
     */
    protected ?string $foreignKey = null;

    /**
     * @var array
     */
    protected array $select = [];

    /**
     * @var array
     */
    protected array $join = [];

    /**
     * @var array
     */
    protected array $where = [];

    /**
     * @var array
     */
    protected array $group = [];

    /**
     * @var array
     */
    protected array $having = [];

    /**
     * @var array
     */
    protected array $order = [];

    /**
     * @var array
     */
    protected array $bindings = [];

    /**
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * @var int|null
     */
    protected ?int $offset = null;

    /**
     * @var object|null
     */
    protected ?object $data = null;

    /**
     * @var array
     */
    protected array $reset = [];

    /**
     * @return $this
     */
    public static function query(): self
    {
        return new static();
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $data = clone $this->toObject();
        unset($data->{$this->primaryKey});

        $this->data = $data;
        $this->reset = [];
    }

    /**
     * @return object
     */
    public function toObject(): object
    {
        return Obj::fromArray($this->data);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if (isset($this->data->{$name})) {
            return $this->data->{$name};
        }

        return null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, mixed $value): void
    {
        if ('database' === $name) {
            return;
        }

        $this->data = Obj::fromArray($this->data);
        $this->data->{$name} = $value;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set($offset, $value);
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->data->{$name});
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->__unset($offset);
    }

    /**
     * @param string $name
     */
    public function __unset(string $name): void
    {
        unset($this->data->{$name});
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return Obj::toArray($this->data);
    }

    /**
     * @throws \Exception
     *
     * @return int
     */
    public function rowCount(): int
    {
        return $this->buildSqlStatement()->rowCount();
    }

    /**
     * @throws \Exception
     *
     * @return \Core\Database\Connection\Statement
     */
    protected function buildSqlStatement(): Statement
    {
        $statement = static::$database->query(
            $this->getQuery(),
            $this->bindings
        );

        $this->clear();

        return $statement;
    }

    /**
     * @param bool $replaceBindings
     *
     * @return string
     */
    public function getQuery(bool $replaceBindings = false): string
    {
        if (empty($this->table)) {
            throw new InvalidArgumentException(
                sprintf('[getQuery] `%s::table` is empty.', get_called_class())
            );
        }

        if (method_exists($this, '_conditions')) {
            $this->_conditions();
        }

        // Build select
        $this->select = implode(', ', ($this->select ?: ["{$this->table}.*"]));
        $sql = "SELECT {$this->select} FROM {$this->table} ";

        // Build join
        if (!empty($this->join) && is_array($this->join)) {
            $this->join = implode(' ', $this->join);
            $sql .= "{$this->join} ";
        }

        // Build where
        if (!empty($this->where) && is_array($this->where)) {
            $this->where = $this->normalizeProperty(implode(' ', $this->where));
            $sql .= "WHERE{$this->where} ";
        }

        // Build group by
        if (!empty($this->group) && is_array($this->group)) {
            $this->group = implode(', ', $this->group);
            $sql .= "GROUP BY {$this->group} ";
        }

        // Build having
        if (!empty($this->having) && is_array($this->having)) {
            $this->having = $this->normalizeProperty(implode(' ', $this->having));
            $sql .= "HAVING{$this->having} ";
        }

        // Build order by
        if (!empty($this->order) && is_array($this->order)) {
            $this->order = implode(', ', $this->order);
            $sql .= "ORDER BY {$this->order} ";
        }

        // Build limit && offset
        if (!empty($this->limit) && is_int($this->limit)) {
            $this->offset = $this->offset ?: '0';

            if (in_array(Config::get('database.default'), ['dblib', 'sqlsrv'])) {
                $sql .= "OFFSET {$this->offset} ROWS FETCH NEXT {$this->limit} ROWS ONLY";
            } else {
                $sql .= "LIMIT {$this->limit} OFFSET {$this->offset}";
            }
        }

        if (true === $replaceBindings) {
            $sql = $this->replaceQueryBindings($sql);
        }

        return trim($sql);
    }

    /**
     * @param string|array $string
     *
     * @return string
     */
    protected function normalizeProperty(array | string $string): string
    {
        if (is_array($string)) {
            $string = implode(' ', $string);
        }

        return preg_replace(
            '/^(and|or)/i', '', trim($string)
        );
    }

    /**
     * @param string $sql
     *
     * @return string
     */
    protected function replaceQueryBindings(string $sql): string
    {
        $keys = array_keys($this->bindings);
        $keys = explode(',', ':'.implode(',:', $keys));
        $values = array_map(function ($bind) {
            if (!is_numeric($bind)) {
                $bind = static::$database->quote($bind);
            }

            return $bind;
        }, array_values($this->bindings));

        return str_replace($keys, $values, $sql);
    }

    /**
     * @param array $properties
     * @param bool  $reset
     *
     * @throws \ReflectionException
     *
     * @return $this
     */
    public function clear(array $properties = [], bool $reset = false): self
    {
        $notReset = array_diff([
            'table',
            'primaryKey',
            'foreignKey',
            'driver',
            'statement',
            'data',
        ], $properties);

        $reflection = new ReflectionClass(get_class($this));

        foreach ($reflection->getProperties() as $property) {
            if (!in_array($property->getName(), $notReset)) {
                if (empty($properties) || in_array($property->getName(), $properties)) {
                    if ($reset) {
                        $this->reset[$property->getName()] = true;
                    } else {
                        $value = null;
                        preg_match('/@var\s+(array|object|string)/im', $property->getDocComment(), $matches);

                        if (!empty($matches[1])) {
                            $value = 'array' === $matches[1] ? [] : new stdClass();
                        }

                        $this->{$property->getName()} = $value;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param array $properties
     *
     * @throws \ReflectionException
     *
     * @return $this
     */
    public function reset(array $properties = []): self
    {
        return $this->clear($properties, true);
    }

    /**
     * @param string $column
     *
     * @throws \Exception
     *
     * @return int
     */
    public function count(string $column = '1'): int
    {
        $row = $this->select("COUNT({$column}) AS count")
            ->order('count DESC')->limit(1)
            ->buildSqlStatement()
            ->fetch(PDO::FETCH_OBJ)
        ;

        return $row ? (int)$row->count : 0;
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return $this
     */
    public function limit(int $limit, int $offset = 0): self
    {
        if (is_numeric($limit)) {
            $this->limit = (int)$limit;

            if (is_numeric($offset)) {
                $this->offset($offset);
            }
        }

        return $this;
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function offset(int $offset): self
    {
        $this->offset = (int)$offset;

        return $this;
    }

    /**
     * @param array|string $order
     *
     * @return $this
     */
    public function order(array | string $order): self
    {
        $this->mountProperty($order, 'order');

        return $this;
    }

    /**
     * @param string|array|null $conditions
     * @param string            $property
     *
     * @return void
     */
    protected function mountProperty(array | string | null $conditions, string $property): void
    {
        if (!is_array($this->{$property})) {
            $this->{$property} = [];
        }

        foreach ((array)$conditions as $condition) {
            if (!empty($condition) && !array_search($condition, $this->{$property})) {
                $this->{$property}[] = trim((string)$condition);
            }
        }
    }

    /**
     * @param array|string $select
     *
     * @return $this
     */
    public function select(array | string $select = '*'): self
    {
        if (is_string($select)) {
            $select = explode(',', $select);
        }

        $this->mountProperty($select, 'select');

        return $this;
    }

    /**
     * @param string|null $table
     *
     * @return $this|string
     */
    public function table(?string $table = null): string | self
    {
        if (!empty($table)) {
            $this->table = (string)$table;

            return $this;
        }

        return $this->table;
    }

    /**
     * @param array|string $join
     *
     * @return $this
     */
    public function join(array | string $join): self
    {
        $this->mountProperty($join, 'join');

        return $this;
    }

    /**
     * @param array|string $group
     *
     * @return $this
     */
    public function group(array | string $group): self
    {
        $this->mountProperty($group, 'group');

        return $this;
    }

    /**
     * @param array|string $having
     *
     * @return $this
     */
    public function having(array | string $having): self
    {
        $this->mountProperty($having, 'having');

        return $this;
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
        return self::$database->transaction($callback, $this);
    }

    /**
     * @param object|array $data
     * @param bool         $validate
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function save(object | array $data = [], bool $validate = true): self
    {
        $this->data($data);
        $primaryValue = $this->getPrimaryValue($this->data);

        if (!$primaryValue && !empty($this->bindings[$this->getPrimaryKey()])) {
            $primaryValue = $this->bindings[$this->getPrimaryKey()];
        }

        if ($primaryValue && $row = $this->fetchById($primaryValue)) {
            $row->update($this->data, $validate);

            return $row;
        }

        return $this->create($this->data, $validate);
    }

    /**
     * @param object|array $data
     *
     * @return string|null
     */
    public function getPrimaryValue(object | array $data = []): ?string
    {
        $data = Obj::toArray($data);

        if (!empty($data[$this->primaryKey])) {
            return $data[$this->primaryKey];
        }

        return $this->{$this->primaryKey} ?? null;
    }

    /**
     * @return string|null
     */
    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }

    /**
     * @param int|string $id
     *
     * @throws \Exception
     *
     * @return $this|null
     */
    public function fetchById(int | string $id): ?self
    {
        if (empty($this->primaryKey)) {
            throw new \Exception(sprintf('Primary key is not configured in the model (%s).', static::class));
        }

        array_unshift($this->where, "AND {$this->table}.{$this->primaryKey} = :u{$this->primaryKey}");
        $this->bindings["u{$this->primaryKey}"] = Common::filterRequestValues($id)[0];

        return $this->fetch();
    }

    /**
     * @throws \Exception
     *
     * @return $this|array|null
     */
    public function fetch(): array | self | null
    {
        $statement = $this->buildSqlStatement();
        $row = $statement->fetch(get_called_class()) ?: null;

        if ($row && method_exists($this, '_row')) {
            $this->_row($row);
        }

        $statement->closeCursor();

        return $row;
    }

    /**
     * @param object|array $data
     * @param bool         $validate
     *
     * @throws \Exception
     *
     * @return $this[]|null
     */
    public function update(object | array $data = [], bool $validate = true): ?array
    {
        $this->data($data, $validate);
        $this->mountWherePrimaryKey();

        if (empty($this->where)) {
            throw new InvalidArgumentException(
                sprintf('[update] `%s::where()` is empty.', get_called_class())
            );
        }

        $rows = static::$database->update(
            $this->table,
            Obj::toArray($this->data),
            "WHERE {$this->normalizeProperty($this->where)}",
            $this->bindings
        );

        $this->clear();

        if (!$rows) {
            return null;
        }

        foreach ($rows as $key => $row) {
            $new = new static();
            $new->data = $row;
            $rows[$key] = $new;
        }

        return $rows;
    }

    /**
     * @param object|array $data
     * @param bool         $validate
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function data(object | array $data = [], bool $validate = true): self
    {
        $data = array_merge(
            Obj::toArray($this->data),
            Obj::toArray($data)
        );

        if (method_exists($this, '_data')) {
            $this->_data($data, $validate);
        }

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * @return void
     */
    protected function mountWherePrimaryKey(): void
    {
        if (!empty($this->getPrimaryValue())) {
            $this->where[] = "AND {$this->table}.{$this->getPrimaryKey()} = :pkey";
            $this->bindings['pkey'] = $this->getPrimaryValue();
        }
    }

    /**
     * @param object|array $data
     * @param bool         $validate
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function create(object | array $data = [], bool $validate = true): self
    {
        $this->data($data, $validate);

        $lastInsertId = static::$database->create($this->table, Obj::toArray($this->data));

        if ($lastInsertId && $this->primaryKey) {
            $row = $this->fetchById($lastInsertId);
        } else {
            foreach ($this->data as $key => $value) {
                $this->whereBy($key, $value);
            }

            $row = $this->fetch();
        }

        $this->clear(['data']);

        return $row;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function whereBy(string $column, mixed $value): self
    {
        $parsedColumn = $column;

        if (!str_contains($column, '.')) {
            $parsedColumn = "{$this->table}.{$column}";
        } else {
            list(, $column) = explode('.', $column);
        }

        $this->where("AND {$parsedColumn} = :{$column}");
        $this->bindings([$column => $value]);

        return $this;
    }

    /**
     * @param string|array $where
     * @param null         $bindings
     *
     * @return $this
     */
    public function where(array | string $where, $bindings = null): self
    {
        $this->mountProperty($where, 'where');
        $this->bindings($bindings);

        return $this;
    }

    /**
     * @param array|string|null $bindings
     *
     * @return $this
     */
    public function bindings(array | string | null $bindings): self
    {
        Common::parseStr($bindings, $this->bindings);

        return $this;
    }

    /**
     * @param array $ids
     *
     * @throws \Exception
     *
     * @return self[]
     */
    public function fetchByIds(array $ids): array
    {
        array_unshift(
            $this->where,
            sprintf("AND {$this->table}.{$this->primaryKey} IN (%s)", implode(',', $ids))
        );

        return $this->fetchAll();
    }

    /**
     * @throws \Exception
     *
     * @return $this[]
     */
    public function fetchAll(): array
    {
        $statement = $this->buildSqlStatement();
        $rows = $statement->fetchAll(\PDO::FETCH_CLASS, get_called_class());

        if (method_exists($this, '_row')) {
            foreach ($rows as $index => $row) {
                $this->_row($row);
            }
        }

        $statement->closeCursor();

        return $rows;
    }

    /**
     * @return string|null
     */
    public function getForeignKey(): ?string
    {
        return $this->foreignKey;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return string|null
     */
    public function getDriver(): ?string
    {
        return $this->driver ?? null;
    }

    /**
     * @param int|null $id
     *
     * @throws \Exception
     *
     * @return $this[]|null
     */
    public function delete(?int $id = null): ?array
    {
        if (!empty($id) && !is_array($id) && $this->primaryKey) {
            $this->data([$this->primaryKey => $id]);
        }

        $this->mountWherePrimaryKey();

        if (empty($this->where)) {
            throw new InvalidArgumentException(
                sprintf('[delete] `%s::where()` is empty.', get_called_class())
            );
        }

        $rows = static::$database->delete(
            $this->table,
            "WHERE {$this->normalizeProperty($this->where)}",
            $this->bindings
        );

        if (!$rows) {
            return null;
        }

        foreach ($rows as $key => $row) {
            $new = new static();
            $new->data = $row;
            $rows[$key] = $new;
        }

        return $rows;
    }

    /**
     * @throws \Exception
     *
     * @return \Core\Database\Connection\Statement
     */
    public function getStatement(): Statement
    {
        $statement = static::$database->prepare($this->getQuery());
        $statement->bindValues($this->bindings);

        return $statement;
    }

    /**
     * @param string $driver
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function driver(string $driver): self
    {
        $this->driver = $driver;

        self::setDatabase(self::$database->driver($this->driver));

        return $this;
    }

    /**
     * @param \Core\Database\Database $database
     */
    public static function setDatabase(Database $database): void
    {
        if (is_null(static::$database)) {
            static::$database = $database;
        }
    }

    /**
     * @return \Core\Database\Database|null
     */
    public function getDatabase(): ?Database
    {
        return static::$database;
    }
}
