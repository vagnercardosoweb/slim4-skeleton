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

use Core\Cache\Cache;
use Core\Config;
use Core\Contracts\ServiceProvider;
use Core\Facades\Facade;
use DI\Container;

/**
 * Class CacheProvider.
 */
class CacheProvider implements ServiceProvider
{
    /**
     * @param \DI\Container $container
     *
     * @return \Core\Cache\Cache
     */
    public function __invoke(Container $container): Cache
    {
        Facade::setAliases(['Cache' => Cache::class]);

        return new Cache(Config::get('cache'));
    }
}
