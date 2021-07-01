<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 10/05/2021 Vagner Cardoso
 */

namespace Core\Contracts;

/**
 * Interface Cache.
 */
interface Cache
{
    /**
     * @param string     $key
     * @param mixed|null $default
     * @param int        $seconds
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null, int $seconds = 0): mixed;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $seconds
     *
     * @return bool
     */
    public function set(string $key, mixed $value, int $seconds = 0): bool;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * @return void
     */
    public function flush(): void;

    /**
     * @param string $key
     * @param int    $value
     *
     * @return bool
     */
    public function increment(string $key, int $value = 1): bool;

    /**
     * @param string $key
     * @param int    $value
     *
     * @return bool
     */
    public function decrement(string $key, int $value = 1): bool;
}
