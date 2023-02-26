<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2023 Vagner Cardoso
 */

namespace Core\Facades;

/**
 * Class Database.
 *
 * @method static \Core\Database\Database             addConnection(string $driver, array $connection)
 * @method static \Core\Database\Database             connection(?string $driver = null)
 * @method static \PDO                                getPdo()
 * @method static mixed                               transaction(\Closure $callback)
 * @method static ?int                                create(string $table, object | array $data)
 * @method static \Core\Database\Connection\Statement query(string $sql, $bindings = null, array $driverOptions = [])
 * @method static ?array                              update(string $table, object | array $data, string $condition, $bindings = null)
 * @method static \Core\Database\Connection\Statement read(string $table, ?string $condition = null, $bindings = null)
 * @method static ?array                              delete(string $table, string $condition, $bindings = null)
 */
class Database extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return \Core\Database\Database::class;
    }
}
