<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

namespace App\Providers;

use Core\Database\Database;
use Core\Facades\Facade;
use Core\Interfaces\ServiceProvider;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

/**
 * Class PDOProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class PDOProvider implements ServiceProvider
{
    /**
     * @param Container $container
     *
     * @throws DependencyException
     * @throws NotFoundException
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
