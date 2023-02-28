<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 28/02/2023 Vagner Cardoso
 */

namespace App\Providers;

use Core\Config;
use Core\Database\Database;
use Core\Database\Model;
use Core\Facades\Facade;
use Core\Interfaces\ServiceProvider;
use DI\Container;

class DatabaseProvider implements ServiceProvider
{
    public function __invoke(Container $container): Database
    {
        Facade::setAliases(['Database' => Database::class]);

        $database = new Database();
        $database->setDefaultDriver(Config::get('database.default', 'pgsql'));

        foreach (Config::get('database.connections') as $driver => $config) {
            $database->addConnection($driver, $config);
        }

        $connection = $database->connection();
        Model::setDatabase($connection);

        return $connection;
    }
}
