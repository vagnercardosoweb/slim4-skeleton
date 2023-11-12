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

use Core\Config;
use Core\Facades\Facade;
use Core\Interfaces\ServiceProvider;
use DI\Container;
use Predis\Client;

class RedisProvider implements ServiceProvider
{
    public function __invoke(Container $container): Client
    {
        Facade::setAliases(['Redis' => Client::class]);

        return new Client(Config::get('redis'));
    }
}
