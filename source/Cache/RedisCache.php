<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 07/05/2021 Vagner Cardoso
 */

namespace Core\Cache;

use Core\Contracts\Cache;
use Core\Support\Common;
use Predis\Client;

/**
 * Class RedisCache.
 */
class RedisCache implements Cache
{
    /**
     * Cache constructor.
     *
     * @param \Predis\Client $client
     * @param string|null    $prefix
     */
    public function __construct(
        protected Client $client,
        protected ?string $prefix = null
    ) {
    }

    /**
     * @param string     $key
     * @param mixed|null $default
     * @param int        $seconds
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null, int $seconds = 0): mixed
    {
        $value = $this->client->get($key);

        if (empty($value)) {
            $value = $default instanceof \Closure ? $default() : $default;

            if ($value) {
                $this->set($key, $value, $seconds);
            }
        }

        return is_string($value) ? Common::unserialize($value) : $value;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return !is_null($this->client->get($key));
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $seconds
     *
     * @return bool
     */
    public function set(string $key, mixed $value, int $seconds = 0): bool
    {
        $value = Common::serialize($value);

        if ($seconds <= 0) {
            return (bool)$this->client->set($key, $value);
        }

        return (bool)$this->client->setex($key, $seconds, $value);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool
    {
        return (bool)$this->client->del($key);
    }

    /**
     * @return void
     */
    public function flush(): void
    {
        foreach ($this->client->keys('*') as $key) {
            $this->client->del(str_replace($this->prefix, '', $key));
        }
    }

    /**
     * @param string $key
     * @param int    $value
     *
     * @return mixed
     */
    public function increment(string $key, int $value = 1): bool
    {
        return (bool)$this->client->incrby($key, $value);
    }

    /**
     * @param string $key
     * @param int    $value
     *
     * @return mixed
     */
    public function decrement(string $key, int $value = 1): bool
    {
        return (bool)$this->client->decrby($key, $value);
    }
}
