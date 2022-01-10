<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 09/01/2022 Vagner Cardoso
 */

namespace App\Providers;

use Core\Config;
use Core\Contracts\ServiceProvider;
use Core\Facades\Facade;
use DI\Container;
use Predis\Client;

/**
 * Class RedisProvider.
 */
class RedisProvider implements ServiceProvider
{
    /**
     * @param \DI\Container $container
     *
     * @return \Predis\Client
     */
    public function __invoke(Container $container): Client
    {
        Facade::setAliases(['Redis' => Client::class]);

        return new Client(Config::get('redis'));
    }
}
