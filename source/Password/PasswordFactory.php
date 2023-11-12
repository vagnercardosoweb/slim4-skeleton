<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

namespace Core\Password;

/**
 * Class PasswordFactory.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class PasswordFactory
{
    /**
     * @param string $driver
     * @param bool   $verifyAlgorithm
     *
     * @return \Core\Password\Password
     */
    public static function create(string $driver = 'bcrypt', bool $verifyAlgorithm = false): Password
    {
        if ('argon2i' === $driver) {
            return new Argon2i($verifyAlgorithm);
        }

        if ('argon2id' === $driver) {
            return new Argon2Id($verifyAlgorithm);
        }

        return new Bcrypt($verifyAlgorithm);
    }
}
