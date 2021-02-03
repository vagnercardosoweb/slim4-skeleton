<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 03/02/2021 Vagner Cardoso
 */

namespace App\Providers;

use Core\Contracts\ServiceProvider;
use Core\Helpers\Env;
use Core\Helpers\Jwt;
use DI\Container;

/**
 * Class JwtProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class JwtProvider implements ServiceProvider
{
    /**
     * @param \DI\Container $container
     *
     * @return \Core\Helpers\Jwt
     */
    public function __invoke(Container $container): Jwt
    {
        $key = Env::get('APP_KEY');

        return new Jwt($key);
    }
}
