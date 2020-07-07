<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 07/07/2020 Vagner Cardoso
 */

namespace Core\Helpers;

/**
 * Class Path.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Path
{
    public static function public_html(?string $path = null): string
    {
        return self::make('PUBLIC_PATH', 'public_html', self::base(), $path);
    }

    public static function base(?string $path = null): string
    {
        if (defined('BASE_PATH')) {
            return constant('BASE_PATH');
        }

        if (!isset($_SERVER['DOCUMENT_ROOT'])) {
            throw new \RuntimeException(
                'Constant [BASE_PATH] not defined.'.
                'Or [DOCUMENT_ROOT] server not exists.'
            );
        }

        define('BASE_PATH', realpath($_SERVER['DOCUMENT_ROOT']));

        return self::normalizePath(constant('BASE_PATH'), $path);
    }

    public static function resources(?string $path = null): string
    {
        return self::make('RESOURCE_PATH', 'resources', self::app(), $path);
    }

    public static function config(?string $path = null): string
    {
        return self::make('CONFIG_PATH', 'config', self::app(), $path);
    }

    public static function routes(?string $path = null): string
    {
        return self::make('ROUTE_PATH', 'routes', self::app(), $path);
    }

    public static function storage(?string $path = null): string
    {
        return self::make('STORAGE_PATH', 'storage', self::app(), $path);
    }

    public static function app(?string $path = null): string
    {
        return self::make('APP_PATH', 'application', self::base(), $path);
    }

    protected static function make(
        string $name,
        string $folder,
        string $root,
        ?string $path = null
    ): string {
        if (!defined($name)) {
            define($name, self::normalizePath($root, $folder));
        }

        return self::normalizePath(constant($name), $path);
    }

    protected static function normalizePath(string $root, ?string $path = null): string
    {
        return rtrim(sprintf('%s/%s', $root, trim($path, '\/')), '\/');
    }
}
