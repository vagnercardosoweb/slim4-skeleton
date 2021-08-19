<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 19/08/2021 Vagner Cardoso
 */

namespace Core\Cache;

use Core\Contracts\Cache;
use Core\Support\Common;

/**
 * Class FileCache.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class FileCache implements Cache
{
    /**
     * FileCache constructor.
     *
     * @param string   $directory
     * @param int|null $permission
     */
    public function __construct(
        protected string $directory,
        protected ?int $permission = null
    ) {
        $this->setPermission($this->permission);
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * @param string $directory
     *
     * @return FileCache
     */
    public function setDirectory(string $directory): FileCache
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPermission(): int | null
    {
        return $this->permission;
    }

    /**
     * @param int|null $permission
     *
     * @return FileCache
     */
    public function setPermission(?int $permission): FileCache
    {
        $permission = $permission ?? 0755;

        $this->permission = $permission;

        return $this;
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
        $value = $this->getPayload($key)['data'];

        if (empty($value)) {
            $value = $default instanceof \Closure ? $default() : $default;

            if ($value) {
                $this->set($key, $value, $seconds);
            }
        }

        return is_string($value)
            ? Common::unserialize($value)
            : $value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $seconds
     *
     * @return mixed
     */
    public function set(string $key, mixed $value, int $seconds = 0): bool
    {
        $path = $this->getPath($key);
        $dirname = dirname($path);

        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
        }

        $data = sprintf('%s%s', $this->setExpireAt($seconds), Common::serialize($value));
        $result = file_put_contents($path, $data);

        if (false !== $result && $result > 0 && !is_null($this->permission)) {
            @chmod($path, $this->permission);

            return true;
        }

        return false;
    }

    /**
     * @return void
     */
    public function flush(): void
    {
        $this->deleteDirectory($this->directory);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return !is_null($this->getPayload($key)['data']);
    }

    /**
     * @param string $key
     * @param int    $value
     *
     * @return mixed
     */
    public function increment(string $key, int $value = 1): bool
    {
        $data = $this->getPayload($key);

        if (!is_int($data['data'])) {
            throw new \UnexpectedValueException(
                "Cache [{$key}] content must be an int."
            );
        }

        return $this->set(
            $key,
            $data['data'] + $value,
            $data['time'] ?? 0
        );
    }

    /**
     * @param string $key
     * @param int    $value
     *
     * @return mixed
     */
    public function decrement(string $key, int $value = 1): bool
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * @param array|string $key
     *
     * @return bool
     */
    public function delete(array | string $key): bool
    {
        return $this->deleteDirectory(
            dirname($this->getPath($key), 2)
        );
    }

    /**
     * @return int
     */
    protected function getCurrentTimestamp(): int
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
        $hash = sha1($key);

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
    protected function setExpireAt(int $seconds): int
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
            $content = Common::unserialize(substr($data, 10));
        } catch (\Exception) {
            $this->delete($key);

            return $this->getEmptyPayload();
        }

        if ($this->getCurrentTimestamp() >= $expiration) {
            $this->delete($key);

            return $this->getEmptyPayload();
        }

        $expiration = $expiration - $this->getCurrentTimestamp();

        return [
            'data' => $content,
            'time' => $expiration,
        ];
    }

    /**
     * @return array
     */
    protected function getEmptyPayload(): array
    {
        return ['data' => null, 'time' => null];
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
            if (
                '.gitkeep' !== $iterator->getFilename()
                && !$iterator->isDot()
            ) {
                if ($iterator->isDir()) {
                    rmdir($iterator->getPathname());
                } else {
                    unlink($iterator->getPathname());
                }
            }

            $iterator->next();
        }

        // rmdir($directory);

        return true;
    }
}
