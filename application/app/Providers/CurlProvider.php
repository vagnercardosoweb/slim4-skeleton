<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 14/04/2021 Vagner Cardoso
 */

namespace App\Providers;

use Core\Contracts\ServiceProvider;
use Core\Curl\Curl;
use Core\Facades\Facade;
use DI\Container;

/**
 * Class CurlProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class CurlProvider implements ServiceProvider
{
    /**
     * @param \DI\Container $container
     *
     * @return \Core\Curl\Curl
     */
    public function __invoke(Container $container): Curl
    {
        Facade::setAliases(['Curl' => Curl::class]);

        return new Curl();
    }
}
