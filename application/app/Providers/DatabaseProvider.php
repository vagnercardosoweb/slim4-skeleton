<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/07/2021 Vagner Cardoso
 */

namespace App\Providers;

use Core\Config;
use Core\Contracts\ServiceProvider;
use Core\Database\Database;
use Core\Facades\Facade;
use DI\Container;

/**
 * Class DatabaseProvider.
 */
class DatabaseProvider implements ServiceProvider
{
    /**
     * @param \DI\Container $container
     *
     * @throws \Exception
     *
     * @return \Core\Database\Database
     */
    public function __invoke(Container $container): Database
    {
        Facade::setAliases(['Database' => Database::class]);

        $database = new Database();
        $database->setDefaultDriver(Config::get('database.default', 'mysql'));

        foreach (Config::get('database.connections') as $driver => $config) {
            $database->addConnection($driver, $config);
        }

        return $database->connection();
    }
}
