<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 06/11/2023 Vagner Cardoso
 */

namespace Core\Facades;

use Closure;

/**
 * @method static bool has(string $key)
 * @method static null|array get(string $key, null|array|Closure $default, int $seconds = 0)
 * @method static bool set(string $key, null|array $value, int $seconds = 0)
 * @method static bool delete(string $key)
 * @method static void flush()
 * @method static bool increment(string $key, int $value)
 * @method static bool decrement(string $key, int $value)
 */
class Cache extends Facade
{
    protected static function getAccessor(): string
    {
        return \Core\Cache\Cache::class;
    }
}
