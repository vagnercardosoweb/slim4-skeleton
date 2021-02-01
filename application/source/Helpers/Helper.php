<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/02/2021 Vagner Cardoso
 */

namespace Core\Helpers;

/**
 * Class Helper.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Helper
{
    /**
     * @param $value
     *
     * @return float
     */
    public static function normalizeFloat($value): float
    {
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return (float)$value;
    }

    /**
     * @param $value
     *
     * @return float|int|bool|array|object|string|null
     */
    public static function normalizeValue($value): float | null | int | bool | array | object | string
    {
        if (is_array($value) || is_object($value)) {
            return $value;
        }

        if (is_integer($value)) {
            return (int)$value;
        }

        if (is_float($value)) {
            return self::normalizeFloat($value);
        }

        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $value
        };
    }
}
