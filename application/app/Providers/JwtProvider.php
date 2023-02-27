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
use Core\Facades\Facade;
use Core\Support\Env;
use Core\Support\Jwt;
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
     * @return \Core\Support\Jwt
     */
    public function __invoke(Container $container): Jwt
    {
        Facade::setAliases(['Jwt' => Jwt::class]);

        $key = Env::get('APP_KEY');

        return new Jwt($key);
    }
}
