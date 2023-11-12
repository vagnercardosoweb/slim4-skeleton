<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

namespace Core\Facades;

/**
 * @method static string encode(array $payload, int $exp = 0)
 * @method static array  decode(string $token)
 */
class Jwt extends Facade
{
    protected static function getAccessor(): string
    {
        return \Core\Support\Jwt::class;
    }
}
