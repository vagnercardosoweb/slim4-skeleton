<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2023 Vagner Cardoso
 */

namespace Core\Cache;

use Core\Contracts\Cache;
use Predis\Client;

readonly class RedisCache implements Cache
{
    public function __construct(
        private Client $client,
    ) {
    }

    public function has(string $key): bool
    {
        return null !== $this->client->get($key);
    }

    public function get(string $key, array|null|\Closure $default = null, int $seconds = 0): array|null
    {
        $value = $this->client->get($key);

        if (empty($value) && $default instanceof \Closure) {
            if (!empty($value = $default())) {
                $this->set($key, $value, $seconds);
            }
        }

        if (is_array($default) && empty($value)) {
            return $default;
        }

        if (is_array($value)) {
            return $value;
        }

        return json_decode($value, true);
    }

    public function set(string $key, array|null $value, int $seconds = 0): bool
    {
        if (empty($value)) {
            return false;
        }

        $value = json_encode($value);

        if ($seconds <= 0) {
            $status = $this->client->set($key, $value);
        } else {
            $status = $this->client->setex($key, $seconds, $value);
        }

        return 'OK' == $status;
    }

    public function delete(string $key): bool
    {
        return $this->client->del($key) > 0;
    }

    public function flush(): void
    {
        foreach ($this->client->keys('*') as $key) {
            $this->client->del($key);
        }
    }

    public function increment(string $key, int $value = 1): bool
    {
        return $this->client->incrby($key, $value) > 0;
    }

    public function decrement(string $key, int $value = 1): bool
    {
        return $this->client->decrby($key, $value) > 0;
    }
}
