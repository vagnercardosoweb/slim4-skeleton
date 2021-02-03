<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 03/02/2021 Vagner Cardoso
 */

namespace Core\Support;

/**
 * Class Str.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Str extends \Illuminate\Support\Str
{
    /**
     * Limit number of letters in a string.
     *
     * @param string $string $string
     * @param int    $limit
     * @param string $end
     *
     * @return string
     */
    public static function chars(string $string, int $limit = 50, string $end = '...'): string
    {
        if (strlen($string) <= $limit) {
            return $string;
        }

        $length = strrpos(self::substr($string, 0, $limit), ' ');

        return self::substr($string, 0, $length).$end;
    }

    /**
     * @param string $value
     *
     * @return string|null
     */
    public static function removeSpaces(string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return trim(preg_replace('/\s+/', '', $value));
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public static function uuid(): string
    {
        $hexBytes = strtolower(Str::randomHexBytes());

        return sprintf('%08s-%04s-%04x-%04x-%012s',
            // 32 bits for "time_low"
            substr($hexBytes, 0, 8),
            // 16 bits for "time_mid"
            substr($hexBytes, 8, 4),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            hexdec(substr($hexBytes, 12, 3)) & 0x0fff | 0x4000,
            // 16 bits:
            // * 8 bits for "clk_seq_hi_res",
            // * 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            hexdec(substr($hexBytes, 16, 4)) & 0x3fff | 0x8000,
            // 48 bits for "node"
            substr($hexBytes, 20, 12)
        );
    }

    /**
     * @param int $length
     *
     * @return string
     */
    public static function randomHexBytes(int $length = 32): string
    {
        $length = (intval($length) <= 8 ? 32 : $length);

        $hashed = bin2hex(random_bytes($length));

        return mb_substr($hashed, 0, $length);
    }
}
