<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2023 Vagner Cardoso
 */

namespace Core\Database\Connection;

use InvalidArgumentException;

/**
 * Class SQLiteConnection.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class SQLiteConnection extends Connection
{
    /**
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config): string
    {
        if ('memory' == $config['database']) {
            return 'sqlite::memory:';
        }

        return "sqlite:{$config['database']}";
    }

    /**
     * @param array $config
     */
    protected function validateConfig(array $config): void
    {
        if (empty($config['database'])) {
            throw new InvalidArgumentException(
                "'sqlite' database not configured."
            );
        }

        if ('memory' !== $config['database'] && !file_exists($config['database'])) {
            throw new InvalidArgumentException(
                "'sqlite' database not exists in path {$config['database']}"
            );
        }
    }

    /**
     * @param array $config
     */
    protected function setSchema(array $config): void
    {
        // TODO
    }

    /**
     * @param array $config
     */
    protected function setEncoding(array $config): void
    {
        // TODO
    }

    /**
     * @param array $config
     */
    protected function setTimezone(array $config): void
    {
        // TODO
    }
}
