<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 14/04/2021 Vagner Cardoso
 */

namespace Core\Database\Connection;

use Core\Support\Common;
use Core\Support\Obj;

/**
 * Class Statement.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Statement extends \PDOStatement
{
    /**
     * @var \PDO
     */
    protected \PDO $pdo;

    /**
     * @var array
     */
    protected array $bindings = [];

    /**
     * Statement constructor.
     *
     * @param \PDO $pdo
     */
    protected function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function lastInsertId($name = null): string
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * @return int
     */
    public function rowCount(): int
    {
        $rowCount = parent::rowCount();

        if (-1 === $rowCount) {
            $rowCount = count($this->fetchAll());
        }

        return (int)$rowCount;
    }

    /**
     * @param mixed $mode
     * @param int   $cursorOrientation
     * @param int   $cursorOffset
     *
     * @return mixed
     */
    public function fetch($mode = null, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset = 0): mixed
    {
        if ($this->isFetchObject($mode) || class_exists($mode)) {
            if (!class_exists($mode)) {
                $mode = 'stdClass';
            }

            return parent::fetchObject($mode);
        }

        return parent::fetch($mode, $cursorOrientation, $cursorOffset);
    }

    /**
     * @param int $style
     *
     * @return bool
     */
    public function isFetchObject($style = null): bool
    {
        $allowed = [\PDO::FETCH_OBJ, \PDO::FETCH_CLASS];
        $fetchMode = $style ?: $this->pdo->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE);

        if (in_array($fetchMode, $allowed)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getQueryString(): string
    {
        return $this->queryString;
    }

    /**
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * @param array|string|object|null $bindings
     */
    public function bindValues(object | array | string | null $bindings): void
    {
        if (empty($bindings)) {
            return;
        }

        if (is_object($bindings)) {
            $bindings = Obj::toArray($bindings);
        }

        if (is_string($bindings)) {
            Common::parseStr($bindings, $bindings);
        }

        foreach ($bindings as $key => $value) {
            if (is_string($key) && in_array($key, ['limit', 'offset', 'l', 'o'])) {
                $value = (int)$value;
            }

            $key = (is_string($key) ? ":{$key}" : ((int)$key + 1));
            $value = !empty($value) || '0' == $value ? filter_var($value, FILTER_DEFAULT) : null;

            $this->bindValue($key, $value, (is_int($value)
                ? \PDO::PARAM_INT
                : (is_bool($value) ? \PDO::PARAM_BOOL
                    : \PDO::PARAM_STR)));

            $this->bindings[$key] = $value;
        }
    }
}
