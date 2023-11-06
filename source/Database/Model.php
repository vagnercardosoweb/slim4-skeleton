<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 06/11/2023 Vagner Cardoso
 */

namespace Core\Database;

use Core\Config;
use Core\Database\Connection\Statement;
use Core\Support\Common;

/**
 * Class Model.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Model implements \ArrayAccess, \JsonSerializable
{
    /**
     * @var Database|null
     */
    protected static null|Database $database = null;

    /**
     * @var string
     */
    protected string $table;

    /**
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * @var string|null
     */
    protected null|string $foreignKey = null;

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
     * @var array<string, mixed>
     */
    protected array $bindings = [];

    /**
     * @var int
     */
    protected int $limit = 50;

    /**
     * @var int
     */
    protected int $offset = 0;

    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @return void
     */
    public function __clone()
    {
        unset($this->data[$this->primaryKey]);
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
        if (isset($this->data[$name])) {
            return $this->data[$name];
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

        $this->data[$name] = $value;
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
        return isset($this->data[$name]);
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
        unset($this->data[$name]);
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
        return $this->data;
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
        $query = sprintf('SELECT COUNT(%s) AS n FROM %s LIMIT 1;', $column, $this->table);
        $statement = static::$database->query($query);
        $row = $statement->fetch();
        $statement->closeCursor();

        return $row['n'] ?? 0;
    }

    /**
     * @return static
     */
    public static function query(): static
    {
        return new static();
    }

    /**
     * @throws \Exception
     *
     * @return static|null
     */
    public function fetch(): null|static
    {
        $statement = $this->getStatement();
        $row = $statement->fetch(get_called_class()) ?: null;
        $statement->closeCursor();

        if ($row && method_exists($this, 'onEachRow')) {
            $this->onEachRow($row);
        }

        return $row;
    }

    /**
     * @throws \Exception
     *
     * @return Statement
     */
    public function getStatement(): Statement
    {
        return static::$database->query($this->getQuery(), $this->bindings);
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        if (empty($this->table)) {
            throw new \InvalidArgumentException(
                sprintf('[getQuery] `%s::table` is empty.', get_called_class())
            );
        }

        if (method_exists($this, 'onBeforeQuery')) {
            $this->onBeforeQuery();
        }

        // Build select
        $select = implode(', ', $this->select ?: ["{$this->table}.*"]);
        $sql = "SELECT {$select} FROM {$this->table} ";

        // Build join
        if (!empty($this->join)) {
            $sql .= sprintf('%s ', implode(' ', $this->join));
        }

        // Build where
        if (!empty($this->where)) {
            $sql .= sprintf('WHERE %s ', trim($this->normalizeProperty(implode(' ', $this->where))));
        }

        // Build group by
        if (!empty($this->group)) {
            $sql .= sprintf('GROUP BY %s ', trim(implode(', ', $this->group)));
        }

        // Build having
        if (!empty($this->having)) {
            $sql .= sprintf('HAVING %s ', trim($this->normalizeProperty(implode(' ', $this->having))));
        }

        // Build order by
        if (!empty($this->order)) {
            $sql .= sprintf('ORDER BY %s ', trim(implode(', ', $this->order)));
        }

        // Build limit && offset
        if (in_array(Config::get('database.default'), ['dblib', 'sqlsrv'])) {
            $sql .= "OFFSET {$this->offset} ROWS FETCH NEXT {$this->limit} ROWS ONLY";
        } else {
            $sql .= "LIMIT {$this->limit} OFFSET {$this->offset}";
        }

        return trim($sql);
    }

    /**
     * @param string|array $string
     *
     * @return string
     */
    protected function normalizeProperty(array|string $string): string
    {
        if (is_array($string)) {
            $string = implode(' ', $string);
        }

        return preg_replace(
            '/^(and|or)/i', '', trim($string)
        );
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return static
     */
    public function limit(int $limit, int $offset = 0): static
    {
        $this->limit = $limit;

        if ($offset > 0) {
            $this->offset($offset);
        }

        return $this;
    }

    /**
     * @param int $offset
     *
     * @return static
     */
    public function offset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param array|string $order
     *
     * @return static
     */
    public function order(array|string $order): static
    {
        $this->mountProperty($order, 'order');

        return $this;
    }

    /**
     * @param string|array<string>|null $conditions
     * @param string                    $property
     *
     * @return void
     */
    protected function mountProperty(null|array|string $conditions, string $property): void
    {
        if (empty($conditions)) {
            return;
        }

        if (!is_array($conditions)) {
            $conditions = [$conditions];
        }

        if (!is_array($this->{$property})) {
            $this->{$property} = [];
        }

        foreach ($conditions as $condition) {
            if (array_search($condition, $this->{$property})) {
                continue;
            }

            $this->{$property}[] = trim($condition);
        }
    }

    /**
     * @param array|string $select
     *
     * @return static
     */
    public function select(array|string $select = '*'): static
    {
        if (is_string($select)) {
            $select = explode(',', $select);
        }

        $this->mountProperty($select, 'select');

        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     *
     * @return Model
     */
    public function table(string $table): static
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param array|string $join
     *
     * @return static
     */
    public function join(array|string $join): static
    {
        $this->mountProperty($join, 'join');

        return $this;
    }

    /**
     * @param array|string $group
     *
     * @return static
     */
    public function group(array|string $group): static
    {
        $this->mountProperty($group, 'group');

        return $this;
    }

    /**
     * @param array|string $having
     *
     * @return static
     */
    public function having(array|string $having): static
    {
        $this->mountProperty($having, 'having');

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
        return static::$database->transaction($callback, $this);
    }

    /**
     * @param array<string, mixed> $data
     * @param bool                 $validate
     *
     * @throws \Exception
     *
     * @return static
     */
    public function save(array $data = [], bool $validate = true): static
    {
        $primaryValue = $this->getPrimaryValue($data);

        if (null === $primaryValue && !empty($this->bindings[$this->getPrimaryKey()])) {
            $primaryValue = $this->bindings[$this->getPrimaryKey()];
        }

        if ($primaryValue && $this->fetchById($primaryValue)) {
            return $this->update($data, $validate)[0];
        }

        return $this->create($data, $validate);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return string|int|null
     */
    public function getPrimaryValue(array $data = []): null|int|string
    {
        if (!empty($data[$this->primaryKey])) {
            return $data[$this->primaryKey];
        }

        $value = $this->{$this->primaryKey};

        return !empty($value) ? $value : null;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @param int|string $id
     *
     * @throws \Exception
     *
     * @return static|null
     */
    public function fetchById(int|string $id): ?static
    {
        if (empty($this->primaryKey)) {
            throw new \Exception(sprintf('Primary key is not configured in the model (%s).', static::class));
        }

        $this->limit = 1;
        array_unshift($this->where, "AND {$this->table}.{$this->primaryKey} = :__pkey_{$this->primaryKey}");
        $this->bindings["__pkey_{$this->primaryKey}"] = Common::filterValues($id)[0];

        return $this->fetch();
    }

    /**
     * @param array<string, mixed> $data
     * @param bool                 $validate
     *
     * @throws \Exception
     *
     * @return array<int, static>|null
     */
    public function update(array $data = [], bool $validate = true): ?array
    {
        $this->data($data, $validate);
        $this->mountWherePrimaryKey();

        if (empty($this->where)) {
            throw new \InvalidArgumentException(
                sprintf('[update] `%s::where()` is empty.', get_called_class())
            );
        }

        $rows = static::$database->update(
            $this->table,
            $this->data,
            $this->normalizeProperty($this->where),
            $this->bindings
        );

        $this->clear();

        if (!$rows) {
            return null;
        }

        foreach ($rows as $index => $row) {
            $new = new static();
            $new->data = $row;
            $rows[$index] = $new;
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $data
     * @param bool                 $validate
     *
     * @throws \Exception
     *
     * @return static
     */
    protected function data(array $data = [], bool $validate = true): static
    {
        $data = array_merge($this->data, $data);

        if (method_exists($this, 'onBeforeData')) {
            $this->onBeforeData($data, $validate);
        }

        $this->data = $data;

        return $this;
    }

    /**
     * @return void
     */
    protected function mountWherePrimaryKey(): void
    {
        if (!empty($this->getPrimaryValue())) {
            $this->where[] = "AND {$this->table}.{$this->getPrimaryKey()} = :__pkey__";
            $this->bindings['__pkey__'] = $this->getPrimaryValue();
        }
    }

    private function clear(): void
    {
        $this->select = [];
        $this->join = [];
        $this->where = [];
        $this->group = [];
        $this->having = [];
        $this->order = [];

        $this->limit = 50;
        $this->offset = 0;

        $this->bindings = [];
        // $this->data = [];
    }

    /**
     * @param array<string, mixed> $data
     * @param bool                 $validate
     *
     * @throws \Exception
     *
     * @return static
     */
    public function create(array $data = [], bool $validate = true): static
    {
        $this->data($data, $validate);
        $result = static::$database->create($this->table, $this->data);
        $this->clear();

        if (is_array($result) && !empty($result)) {
            $this->data = $result;

            return $this;
        }

        if (!empty($result) && !empty($this->primaryKey)) {
            return $this->fetchById($result);
        }

        foreach ($this->data as $key => $value) {
            $this->whereBy($key, $value);
        }

        return $this->fetch();
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @throws \Exception
     *
     * @return static
     */
    public function whereBy(string $column, mixed $value): static
    {
        $parsedColumn = $column;

        if (!str_contains($column, '.')) {
            $parsedColumn = "{$this->table}.{$column}";
        } else {
            list(, $column) = explode('.', $column);
        }

        $this->where("AND {$parsedColumn} = :{$column}");
        $this->bindings[$column] = $value;

        return $this;
    }

    /**
     * @param string|array         $where
     * @param array<string, mixed> $bindings
     *
     * @return static
     */
    public function where(array|string $where, array $bindings = []): static
    {
        $this->mountProperty($where, 'where');
        $this->bindings($bindings);

        return $this;
    }

    /**
     * @param array<string, mixed> $bindings
     *
     * @return static
     */
    public function bindings(array $bindings): static
    {
        $this->bindings = array_merge($this->bindings, $bindings);

        return $this;
    }

    /**
     * @param array $ids
     *
     * @throws \Exception
     *
     * @return array<int, static>
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
     * @return array<int, static>
     */
    public function fetchAll(): array
    {
        $statement = $this->getStatement();
        $rows = $statement->fetchAll(\PDO::FETCH_CLASS, get_called_class());

        if (method_exists($this, 'onEachRow')) {
            array_walk($rows, fn ($row) => $this->onEachRow($row));
        }

        $statement->closeCursor();

        return $rows;
    }

    /**
     * @return string|null
     */
    public function getForeignKey(): null|string
    {
        return $this->foreignKey;
    }

    /**
     * @param int|string|null $id
     *
     * @throws \Exception
     *
     * @return array<int, static>|null
     */
    public function delete(int|string $id = null): ?array
    {
        if (!empty($id) && !empty($this->primaryKey)) {
            $this->data([$this->primaryKey => $id]);
        }

        $this->mountWherePrimaryKey();

        if (empty($this->where)) {
            throw new \InvalidArgumentException(
                sprintf('[delete] `%s::where()` is empty.', get_called_class())
            );
        }

        $rows = static::$database->delete(
            $this->table,
            $this->normalizeProperty($this->where),
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
     * @param string $connection
     *
     * @throws \Exception
     *
     * @return static
     */
    public function connection(string $connection): static
    {
        static::setDatabase(static::$database->connection($connection));

        return $this;
    }

    /**
     * @param Database $database
     */
    public static function setDatabase(Database $database): void
    {
        static::$database = $database;
    }

    /**
     * @return Database|null
     */
    public function getDatabase(): ?Database
    {
        return static::$database;
    }
}
