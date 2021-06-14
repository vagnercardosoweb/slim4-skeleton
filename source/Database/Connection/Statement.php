<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 14/06/2021 Vagner Cardoso
 */

namespace Core\Database\Connection;

/**
 * Class Statement.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Statement extends \PDOStatement
{
    /**
     * @var array
     */
    protected array $bindings = [];

    /**
     * Statement constructor.
     *
     * @param \PDO $pdo
     */
    protected function __construct(protected \PDO $pdo)
    {
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * @param string|null $name
     *
     * @return string
     */
    public function lastInsertId(?string $name = null): string
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

        return $rowCount;
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
     * @param int|null $style
     *
     * @return bool
     */
    public function isFetchObject(int $style = null): bool
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
     * @param array $bindings
     */
    public function bindValues(array $bindings = []): void
    {
        if (empty($bindings)) {
            return;
        }

        foreach ($bindings as $key => $value) {
            if (is_string($key) && in_array($key, ['limit', 'offset', 'l', 'o'])) {
                $value = (int)$value;
            }

            $key = (is_string($key) ? ":{$key}" : ((int)$key + 1));
            $value = !empty($value) || '0' == $value ? filter_var($value, FILTER_DEFAULT) : null;

            $type = \PDO::PARAM_STR;

            if (is_integer($value)) {
                $type = \PDO::PARAM_INT;
            }

            if (is_bool($value)) {
                $type = \PDO::PARAM_BOOL;
            }

            $this->bindValue($key, $value, $type);
            $this->bindings[$key] = $value;
        }
    }
}
