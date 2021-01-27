<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/01/2021 Vagner Cardoso
 */

namespace Core\Handlers;

/**
 * Class ErrorHandler.
 *
 * @author  Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class ErrorHandler
{
    /**
     * @param int|string $code
     *
     * @return string
     */
    public static function getHtmlClass(int | string $code): string
    {
        if (is_string($code) && 200 !== $code) {
            $code = E_USER_ERROR;
        }

        return match ($code) {
            E_USER_NOTICE, E_NOTICE => 'info',
            E_USER_WARNING, E_WARNING => 'warning',
            200 => 'success',
            default => 'danger',
        };
    }
}
