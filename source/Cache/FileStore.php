<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/03/2021 Vagner Cardoso
 */

namespace Core\Cache;

use Core\Helpers\Helper;
use Core\Interfaces\CacheStore;

/**
 * Class FileStore.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class FileStore implements CacheStore
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @var int|null
     */
    protected $permission;

    /**
     * FileStore constructor.
     *
     * @param string   $directory
     * @param int|null $permission
     */
    public function __construct(string $directory, ?int $permission = null)
    {
        $this->directory = $directory;
        $this->permission = $permission;
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @param int    $seconds
     *
     * @return mixed
     */
    public function get(string $key, $default = null, int $seconds = 0)
    {
        $value = $this->getPayload($key)['content'];

        if (empty($value)) {
            $value = $default instanceof \Closure ? $default() : $default;

            if ($seconds > 0) {
                $this->set($key, $value, $seconds);
            }
        }

        return is_string($value)
            ? Helper::unserialize($value)
            : $value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $seconds
     *
     * @return mixed
     */
    public function set(string $key, $value, int $seconds = 0): bool
    {
        $path = $this->getPath($key);
        $dirname = dirname($path);

        if (!file_exists($dirname)) {
            @mkdir($dirname, 0777, true);
        }

        $data = sprintf('%s%s', $this->expiration($seconds), Helper::serialize($value));
        $result = file_put_contents($path, $data);

        if (false !== $result && $result > 0 && !is_null($this->permission)) {
            @chmod($path, $this->permission);

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function flush(): bool
    {
        return $this->deleteDirectory($this->directory);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return !is_null($this->getPayload($key)['content']);
    }

    /**
     * @param string $key
     * @param int    $value
     *
     * @return mixed
     */
    public function increment(string $key, $value = 1): bool
    {
        $data = $this->getPayload($key);

        if (!is_int($data['content'])) {
            throw new \UnexpectedValueException(
                "Cache [{$key}] content must be an int."
            );
        }

        return $this->set(
            $key,
            (int)$data['content'] + $value,
            $data['expiration'] ?? 0
        );
    }

    /**
     * @param string $key
     * @param int    $value
     *
     * @return mixed
     */
    public function decrement(string $key, $value = 1): bool
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * @param string|array $key
     *
     * @return bool
     */
    public function delete($key): bool
    {
        return $this->deleteDirectory(
            dirname($this->getPath($key), 2)
        );
    }

    /**
     * @return int
     */
    public function currentTime(): int
    {
        return (new \DateTime())->getTimestamp();
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function getPath(string $key): string
    {
        $hash = hash('sha256', $key);

        return sprintf('%s/%s/%s',
            $this->directory,
            "{$hash[0]}/{$hash[1]}",
            $hash
        );
    }

    /**
     * @param int $seconds
     *
     * @return int
     */
    protected function expiration(int $seconds): int
    {
        $date = new \DateTime();
        $interval = $date->add(new \DateInterval("PT{$seconds}S"));
        $time = $interval->getTimestamp();

        return $seconds <= 0 || $time > 9999999999 ? 9999999999 : $time;
    }

    /**
     * @param string $key
     *
     * @return array
     */
    protected function getPayload(string $key): array
    {
        $path = $this->getPath($key);

        try {
            $data = file_get_contents($path);
            $expiration = substr($data, 0, 10);
            $content = Helper::unserialize(substr($data, 10));
        } catch (\Exception $e) {
            $this->delete($key);

            return $this->emptyPayload();
        }

        if ($this->currentTime() >= $expiration) {
            $this->delete($key);

            return $this->emptyPayload();
        }

        $expiration = $expiration - $this->currentTime();

        return [
            'content' => $content,
            'expiration' => $expiration,
        ];
    }

    /**
     * @return array
     */
    protected function emptyPayload(): array
    {
        return ['content' => null, 'expiration' => null];
    }

    /**
     * @param string $directory
     *
     * @return bool
     */
    protected function deleteDirectory(string $directory): bool
    {
        if (!is_dir($directory)) {
            return false;
        }

        /** @var \DirectoryIterator $iterator */
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        $iterator->rewind();

        while ($iterator->valid()) {
            if ($iterator->isDir()) {
                @rmdir($iterator->getPathname());
            } else {
                @unlink($iterator->getPathname());
            }

            $iterator->next();
        }

        @rmdir($directory);

        return true;
    }
}
