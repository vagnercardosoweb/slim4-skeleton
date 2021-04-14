<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 14/04/2021 Vagner Cardoso
 */

namespace Core\Facades;

/**
 * Class Jwt.
 *
 * @method static string encode(array $payload, array $header = [])
 * @method static array decode(string $token)
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Jwt extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return \Core\Support\Jwt::class;
    }
}
