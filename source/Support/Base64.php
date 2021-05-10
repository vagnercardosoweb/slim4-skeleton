<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 10/05/2021 Vagner Cardoso
 */

namespace Core\Support;

/**
 * Class Base64.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Base64
{
    /**
     * @param string $data
     *
     * @return string
     */
    public static function encode(string $data): string
    {
        return str_replace(
            '=', '', strtr(
                base64_encode($data), '+/', '-_'
            )
        );
    }

    /**
     * @param string $data
     * @param null   $strict
     *
     * @return bool|string
     */
    public static function decode(string $data, $strict = null): bool | string
    {
        $remainder = strlen($data) % 4;

        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }

        return base64_decode(
            strtr($data, '-_', '+/'), $strict
        );
    }
}
