<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 11/02/2021 Vagner Cardoso
 */

namespace App\Providers;

use Core\Contracts\ServiceProvider;
use Core\Facades\Facade;
use Core\Support\Encryption;
use Core\Support\Env;
use DI\Container;

/**
 * Class EncryptionProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class EncryptionProvider implements ServiceProvider
{
    /**
     * @param \DI\Container $container
     *
     * @return \Core\Support\Encryption
     */
    public function __invoke(Container $container): Encryption
    {
        Facade::setAliases(['Encryption' => Encryption::class]);

        $key = Env::get('APP_KEY');
        $key = str_replace('vcw:', '', $key);
        $key = mb_substr($key, 0, 32);

        return new Encryption($key);
    }
}
