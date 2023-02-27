<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/02/2023 Vagner Cardoso
 */

namespace Core\Contracts;

interface Cache
{
    public function has(string $key): bool;

    public function get(string $key, array|null|\Closure $default, int $seconds): array|null;

    public function set(string $key, array|null $value, int $seconds): bool;

    public function delete(string $key): bool;

    public function flush(): void;

    public function increment(string $key, int $value): bool;

    public function decrement(string $key, int $value): bool;
}
