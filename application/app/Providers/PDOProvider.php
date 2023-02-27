<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/02/2023 Vagner Cardoso
 */

namespace App\Providers;

use Core\Contracts\ServiceProvider;
use Core\Database\Database;
use Core\Facades\Facade;
use DI\Container;

/**
 * Class PDOProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class PDOProvider implements ServiceProvider
{
    /**
     * @param \DI\Container $container
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     *
     * @return \PDO
     */
    public function __invoke(Container $container): \PDO
    {
        Facade::setAliases(['PDO' => \PDO::class]);

        $database = $container->get(Database::class);

        return $database->getPdo();
    }
}
