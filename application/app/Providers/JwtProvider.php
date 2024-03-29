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

use Core\Facades\Facade;
use Core\Interfaces\ServiceProvider;
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
     * @param Container $container
     *
     * @return Jwt
     */
    public function __invoke(Container $container): Jwt
    {
        Facade::setAliases(['Jwt' => Jwt::class]);

        $key = Env::get('APP_KEY');

        return new Jwt($key);
    }
}
