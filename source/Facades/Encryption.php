<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/02/2023 Vagner Cardoso
 */

namespace Core\Facades;

/**
 * Class Encryption.
 *
 * @method static \Core\Support\Encryption setKey(string $key)
 * @method static string                   getKey()
 * @method static \Core\Support\Encryption setCipher(string $cipher)
 * @method static string                   getCipher()
 * @method static bool                     supported(string $key, string $cipher)
 * @method static bool                     generateKey(string $cipher)
 * @method static string                   encrypt(mixed $payload, bool $serialize = true)
 * @method static mixed                    decrypt(string $encrypted, bool $unserialize = true)
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Encryption extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return \Core\Support\Encryption::class;
    }
}
