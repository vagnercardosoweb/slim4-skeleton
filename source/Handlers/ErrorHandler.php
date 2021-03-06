<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 09/07/2021 Vagner Cardoso
 */

namespace Core\Handlers;

/**
 * Class ErrorHandler.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class ErrorHandler extends \Slim\Handlers\ErrorHandler
{
    public const BAD_REQUEST = 'BAD_REQUEST';

    public const NOT_ALLOWED = 'NOT_ALLOWED';

    public const NOT_IMPLEMENTED = 'NOT_IMPLEMENTED';

    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';

    public const SERVER_ERROR = 'SERVER_ERROR';

    public const UNAUTHENTICATED = 'UNAUTHENTICATED';

    public const SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';

    public const INTERNAL_SERVER_ERROR = 'INTERNAL_SERVER_ERROR';

    /**
     * @param int|string $code
     *
     * @return string
     */
    public static function getHtmlClass(int | string $code): string
    {
        if (is_string($code) && 200 != $code) {
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
