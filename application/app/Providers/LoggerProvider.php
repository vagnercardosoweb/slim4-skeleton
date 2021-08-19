<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 19/08/2021 Vagner Cardoso
 */

namespace App\Providers;

use Core\Contracts\ServiceProvider;
use Core\Facades\Facade;
use Core\Logger;
use DI\Container;

/**
 * Class LoggerProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class LoggerProvider implements ServiceProvider
{
    /**
     * @param \DI\Container $container
     *
     * @return \Core\Logger
     */
    public function __invoke(Container $container): Logger
    {
        Facade::setAliases(['Logger' => Logger::class]);

        return new Logger();
    }
}
