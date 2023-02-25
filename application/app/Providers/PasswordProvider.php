<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/02/2023 Vagner Cardoso
 */

namespace App\Providers;

use Core\Contracts\ServiceProvider;
use Core\Facades\Facade;
use Core\Password\Password;
use Core\Password\PasswordFactory;
use Core\Support\Env;
use DI\Container;

/**
 * Class PasswordProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class PasswordProvider implements ServiceProvider
{
    /**
     * @param \DI\Container $container
     *
     * @return \Core\Password\Password
     */
    public function __invoke(Container $container): Password
    {
        Facade::setAliases(['Password' => Password::class]);

        $driver = Env::get('PASSWORD_DEFAULT_DRIVER', 'bcrypt');
        $verifyAlgorithm = Env::get('PASSWORD_VERIFY_ALGORITHM', false);

        return PasswordFactory::create($driver, $verifyAlgorithm);
    }
}
