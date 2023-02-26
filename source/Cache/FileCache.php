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
use Exception;

readonly class FileCache implements Cache
{
    public function __construct(
        private string $directory,
        private int $permission = 0755
    ) {
    }

    public function get(string $key, array|null|\Closure $default = null, int $seconds = 0): array|null
    {
        $value = $this->getPayload($key)['payload'];

        if (empty($value) && $default instanceof \Closure) {
            if (!empty($value = $default())) {
                $this->set($key, $value, $seconds);
            }
        }

        if (is_array($default) && empty($value)) {
            return $default;
        }

        return $value;
    }

    /**
     * @param string $key
     *
     * @return array{expire_time: int, payload: array<string, mixed>}
     */
    private function getPayload(string $key): array
    {
        $path = $this->getPath($key);
        $emptyPayload = $this->getEmptyPayload();

        try {
            $cache = json_decode(file_get_contents($path), true);
            $currentTimestamp = (new \DateTime())->getTimestamp();

            if ($cache['expire_time'] > 0 && $currentTimestamp >= $cache['expire_time']) {
                throw new \DomainException('Cache is expired.');
            }
        } catch (Exception) {
            $this->delete($key);
            $cache = $emptyPayload;
        }

        return $cache;
    }

    private function getPath(string $key): string
    {
        return sprintf('%s/%s.json', $this->directory, sha1($key));
    }

    private function getEmptyPayload(): array
    {
        return ['name' => null, 'expire_time' => 0, 'payload' => null];
    }

    public function delete(array|string $key): bool
    {
        $path = $this->getPath($key);

        if (!file_exists($path)) {
            return false;
        }

        return unlink($path);
    }

    public function set(string $key, array|null $value, int $seconds = 0): bool
    {
        if (empty($value)) {
            return false;
        }

        $path = $this->getPath($key);
        $result = file_put_contents($path, json_encode([
            'key' => $key,
            'full_path' => $path,
            'created_time' => (new \DateTime())->getTimestamp(),
            'expire_time' => $this->getExpireTime($seconds),
            'payload' => $value,
        ]));

        if (false !== $result && $result > 0) {
            @chmod($path, $this->permission);

            return true;
        }

        return false;
    }

    private function getExpireTime(int $seconds): int
    {
        if ($seconds <= 0) {
            return $seconds;
        }

        $date = new \DateTime();
        $interval = $date->add(new \DateInterval("PT{$seconds}S"));

        return $interval->getTimestamp();
    }

    public function flush(): void
    {
        /** @var \DirectoryIterator $iterator */
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->directory), \FilesystemIterator::SKIP_DOTS);
        $iterator->rewind();

        while ($iterator->valid()) {
            if (!$iterator->isDir() && !$iterator->isDot() && '.gitkeep' !== $iterator->getFilename()) {
                unlink($iterator->getPathname());
            }
            $iterator->next();
        }
    }

    public function has(string $key): bool
    {
        return null !== $this->getPayload($key)['payload'];
    }

    public function decrement(string $key, int $value = 1): bool
    {
        return $this->increment($key, $value * -1);
    }

    public function increment(string $key, int $value = 1): bool
    {
        $payload = $this->getPayload($key);

        if (empty($payload['payload']['value'])) {
            $payload['payload'] = ['value' => 0];
        }

        if (!isset($payload['expire_time'])) {
            $payload['expire_time'] = 0;
        }

        $payload['payload']['value'] += $value;

        return $this->set($key, $payload['payload'], $payload['expire_time']);
    }
}
