<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 15/02/2021 Vagner Cardoso
 */

namespace Core\Facades;

/**
 * Class Password.
 *
 * @method static array info(string $hashedValue)
 * @method static bool verify(string $plainValue, string $hashedValue)
 * @method static string make(int | string $password, array $options = [])
 * @method static bool needsRehash(string $hash, array $options = [])
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Password extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return \Core\Password\Password::class;
    }
}
