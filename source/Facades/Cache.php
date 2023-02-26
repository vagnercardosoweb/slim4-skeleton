<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2023 Vagner Cardoso
 */

namespace Core\Facades;

use Closure;

/**
 * Class Cache.
 *
 * @method static array|null get(string $key, array|Closure|null $default = null, int $seconds = 0)
 * @method static bool       has(string $key)
 * @method static bool       set(string $key, array|object $value, int $seconds = 0)
 * @method static bool       delete(string $key)
 * @method static void       flush()
 * @method static bool       increment(string $key, int $value = 1)
 * @method static bool       decrement(string $key, int $value = 1)
 */
class Cache extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return \Core\Cache\Cache::class;
    }
}
