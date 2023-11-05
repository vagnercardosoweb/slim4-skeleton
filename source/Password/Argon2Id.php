<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/11/2023 Vagner Cardoso
 */

namespace Core\Password;

/**
 * Class Argon2Id.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Argon2Id extends Argon2i
{
    /**
     * @return int|string
     */
    public function algorithm(): int|string
    {
        return PASSWORD_ARGON2ID;
    }
}
