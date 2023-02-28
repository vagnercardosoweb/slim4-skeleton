<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 28/02/2023 Vagner Cardoso
 */

namespace Core;

use Core\Interfaces\SessionInterface;
use Core\Support\Env;

class Session implements SessionInterface
{
    private array $storage = [];

    public function __construct()
    {
        if ($this->disabled() || $this->active()) {
            return;
        }

        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';

        session_set_cookie_params([
            'path' => '/',
            'domain' => $domain,
            'httponly' => true,
            'secure' => true,
        ]);

        session_start();

        $this->storage = &$_SESSION;
    }

    private function disabled(): bool
    {
        return false === Env::get('APP_SESSION_ENABLED', true);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        if (isset($this->storage[$name])) {
            return $this->storage[$name];
        }

        return $default;
    }

    private function active(): bool
    {
        return PHP_SESSION_ACTIVE === session_status();
    }

    public function set(string $name, mixed $value): void
    {
        $this->storage[$name] = $value;
    }

    public function remove(string $name): void
    {
        if ($this->has($name)) {
            unset($this->storage[$name]);
        }
    }

    public function has(string $name): bool
    {
        return isset($this->storage[$name]);
    }

    public function all(): array
    {
        return $this->storage;
    }

    public function clear(): void
    {
        $this->storage = [];

        if ($this->disabled()) {
            return;
        }

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                $this->name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_unset();
        session_destroy();

        if ($this->active()) {
            session_regenerate_id(true);
        }

        session_write_close();
    }
}
