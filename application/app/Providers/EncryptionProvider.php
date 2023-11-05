<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/11/2023 Vagner Cardoso
 */

namespace App\Providers;

use Core\Facades\Facade;
use Core\Interfaces\ServiceProvider;
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
     * @param Container $container
     *
     * @return Encryption
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
