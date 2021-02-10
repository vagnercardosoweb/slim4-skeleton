<?php

namespace App\Providers;

use Core\Config;
use Core\Contracts\ServiceProvider;
use Core\Database\Database;
use DI\Container;

/**
 * Class DatabaseProvider
 *
 * @package App\Providers
 */
class DatabaseProvider implements ServiceProvider
{
    /**
     * @param \DI\Container $container
     *
     * @throws \Exception
     * @return \Core\Database\Database
     */
    public function __invoke(Container $container): Database
    {
        $database = new Database();
        $database->setDefaultDriver(Config::get('database.default', 'mysql'));

        foreach (Config::get('database.connections') as $driver => $config) {
            $database->addConnection($driver, $config);
        }

        return $database->connection();
    }
}
