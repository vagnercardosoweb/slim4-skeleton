<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/02/2023 Vagner Cardoso
 */

namespace Core\Database\Connection;

use Core\Contracts\ConnectionEvent;

/**
 * Class Connection.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Connection extends \PDO
{
    /**
     * Default options.
     *
     * @var array
     */
    protected array $options = [
        \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
        \PDO::ATTR_PERSISTENT => false,
        \PDO::ATTR_STRINGIFY_FETCHES => false,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * @param array $config
     *
     * @throws \Exception
     */
    public function __construct(array $config)
    {
        try {
            $this->validateConfig($config);

            list($username, $password) = [
                $config['username'] ?? null,
                $config['password'] ?? null,
            ];

            $dsn = $config['url'] ?? $this->getDsn($config);
            $options = $this->getOptions($config);

            parent::__construct($dsn, $username, $password, $options);

            $this->setStatement();
            $this->setSchema($config);
            $this->setEncoding($config);
            $this->setTimezone($config);
            $this->setAttributesAndCommands($config);
            $this->setEvents($config);
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @return array
     */
    public static function getAvailableDrivers(): array
    {
        return array_intersect(
            self::getSupportedDrivers(),
            str_replace(['pdo_dblib'], 'dblib', \PDO::getAvailableDrivers())
        );
    }

    /**
     * @return array
     */
    public static function getSupportedDrivers(): array
    {
        return ['mysql', 'pgsql', 'sqlite', 'sqlsrv', 'dblib'];
    }

    /**
     * @param array $config
     */
    protected function validateConfig(array $config): void
    {
        $classSplit = explode('\\', get_called_class());
        $className = array_pop($classSplit);

        if (empty($config['host'])) {
            throw new \InvalidArgumentException(
                sprintf('%s: host not configured.', $className)
            );
        }

        if (empty($config['username'])) {
            throw new \InvalidArgumentException(
                sprintf('%s: username not configured.', $className)
            );
        }

        if (empty($config['password']) && empty($config['notPassword'])) {
            throw new \InvalidArgumentException(
                sprintf('%s: password not configured.', $className)
            );
        }

        if (empty($config['database']) && empty($config['notDatabase'])) {
            throw new \InvalidArgumentException(
                sprintf('%s: database not configured.', $className)
            );
        }
    }

    /**
     * @param array $config
     *
     * @return string
     */
    abstract protected function getDsn(array $config): string;

    /**
     * @param array $config
     *
     * @return array
     */
    protected function getOptions(array $config): array
    {
        $options = $config['options'] ?? [];

        return array_diff_key($this->options, $options) + $options;
    }

    /**
     * @return void
     */
    protected function setStatement(): void
    {
        $this->setAttribute(
            \PDO::ATTR_STATEMENT_CLASS,
            [Statement::class, [$this]]
        );
    }

    /**
     * @param array $config
     */
    abstract protected function setSchema(array $config): void;

    /**
     * @param array $config
     */
    abstract protected function setEncoding(array $config): void;

    /**
     * @param array $config
     */
    abstract protected function setTimezone(array $config): void;

    /**
     * @param array $config
     */
    protected function setAttributesAndCommands(array $config): void
    {
        if (!empty($config['attributes'])) {
            foreach ((array)$config['attributes'] as $key => $value) {
                $this->setAttribute($key, $value);
            }
        }

        if (!empty($config['commands'])) {
            foreach ((array)$config['commands'] as $command) {
                $this->exec($command);
            }
        }
    }

    /**
     * @param array $config
     */
    protected function setEvents(array $config): void
    {
        if (!empty($config['events'])) {
            foreach ((array)$config['events'] as $class) {
                if (!is_a($class, ConnectionEvent::class, true)) {
                    throw new \RuntimeException(
                        sprintf('Class %s must implement class %s.', $class, ConnectionEvent::class)
                    );
                }

                call_user_func([new $class(), '__invoke'], $this);
            }
        }
    }
}
