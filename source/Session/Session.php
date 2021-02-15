<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 15/02/2021 Vagner Cardoso
 */

namespace Core\Session;

use Core\Support\Env;
use Core\Support\Obj;
use Core\Support\Path;

/**
 * Class Session.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Session
{
    /**
     * @var string
     */
    protected string $key = 'vcw:session';

    /**
     * @var object
     */
    protected object $storage;

    /**
     * Session constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->start();

        if (!isset($_SESSION)) {
            $this->storage[$this->key] = [];
        } else {
            $this->storage = &$_SESSION[$this->key];
        }

        $this->storage = Obj::fromArray($this->storage);
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function __unset(string $name): void
    {
        $this->remove($name);
    }

    /**
     * @return void
     */
    public function start(): void
    {
        if (!session_id() && PHP_SESSION_NONE === session_status()) {
            $current = $this->cookieParams();
            $cacheLimiter = Env::get('APP_SESSION_CACHE_LIMITER');
            $sessionSave = Env::get('APP_SESSION_SAVE_PATH', false);
            $sessionName = $_SERVER['HTTP_HOST'] ?? 'vcw:session';

            session_set_cookie_params(
                $current['lifetime'],
                $current['path'],
                $current['domain'],
                $current['secure'],
                true
            );

            session_name(md5(sha1($sessionName)));

            if (null !== $cacheLimiter) {
                session_cache_limiter($cacheLimiter);
            }

            if (true === $sessionSave) {
                session_save_path(Path::storage('/sessions'));
            }

            session_start();
        }
    }

    /**
     * @return array
     */
    public function cookieParams(): array
    {
        return session_get_cookie_params();
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $name, $default = null): mixed
    {
        if (isset($this->storage->{$name})) {
            return $this->storage->{$name};
        }

        return $default;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $name, mixed $value): void
    {
        if (is_array($value)) {
            $value = Obj::fromArray($value);
        }

        $this->storage->{$name} = $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->storage->{$name});
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function remove(string $name): void
    {
        if ($this->has($name)) {
            unset($this->storage->{$name});
        }
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return session_id();
    }

    /**
     * @return object
     */
    public function all(): object
    {
        return $this->storage;
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->storage = new \stdClass();

        if (ini_get('session.use_cookies')) {
            $params = $this->cookieParams();

            setcookie(
                $this->name(),
                '',
                (time() - 42000),
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        session_unset();

        $this->regenerate();

        session_write_close();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return session_name();
    }

    /**
     * @return void
     */
    public function regenerate(): void
    {
        if (Session::active()) {
            session_regenerate_id(true);
        }
    }

    /**
     * @return bool
     */
    public static function active(): bool
    {
        return PHP_SESSION_ACTIVE === session_status();
    }
}
