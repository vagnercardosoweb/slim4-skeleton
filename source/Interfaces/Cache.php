<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/11/2023 Vagner Cardoso
 */

namespace Core\Interfaces;

interface Cache
{
    public function has(string $key): bool;

    public function get(string $key, null|array|\Closure $default, int $seconds): null|array;

    public function set(string $key, null|array $value, int $seconds): bool;

    public function delete(string $key): bool;

    public function flush(): void;

    public function increment(string $key, int $value): bool;

    public function decrement(string $key, int $value): bool;
}
