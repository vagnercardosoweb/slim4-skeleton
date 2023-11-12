<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
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
    public array $bindings = [];

    /**
     * Statement constructor.
     *
     * @param \PDO $pdo
     */
    protected function __construct(protected \PDO $pdo) {}

    /**
     * @param string|null $name
     *
     * @return string
     */
    public function lastInsertId(string $name = null): string
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * @param int|string $mode
     * @param int        $cursorOrientation
     * @param int        $cursorOffset
     *
     * @return array<string, mixed>|\stdClass
     */
    public function fetch(int|string $mode = \PDO::FETCH_ASSOC, int $cursorOrientation = \PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        if (is_int($mode) && $this->isFetchObject($mode)) {
            $mode = 'stdClass';
        }

        if (is_string($mode) && class_exists($mode)) {
            return parent::fetchObject($mode);
        }

        return parent::fetch($mode, $cursorOrientation, $cursorOffset);
    }

    /**
     * @param int $style
     *
     * @return bool
     */
    public function isFetchObject(int $style = \PDO::ATTR_DEFAULT_FETCH_MODE): bool
    {
        return in_array($style, [\PDO::FETCH_OBJ, \PDO::FETCH_CLASS]);
    }

    /**
     * @param array $bindings
     */
    public function addBindings(array $bindings = []): void
    {
        if (empty($bindings)) {
            return;
        }

        foreach ($bindings as $key => $value) {
            $key = (is_string($key) ? ":{$key}" : $key + 1);
            $value = !empty($value) || '0' == $value ? filter_var($value) : null;

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
