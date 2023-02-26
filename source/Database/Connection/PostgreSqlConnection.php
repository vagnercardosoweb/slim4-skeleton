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

/**
 * Class PostgreSqlConnection.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class PostgreSqlConnection extends Connection
{
    /**
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config): string
    {
        return sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;options=--application_name=%s',
            $config['host'],
            $config['port'] ?? 5432,
            $config['database'],
            $config['application_name'] ?? 'app'
        );
    }

    /**
     * @param array $config
     */
    protected function setSchema(array $config): void
    {
        $config['schema'] = $config['schema'] ?? 'public';
        $this->exec(sprintf('SET search_path TO %s;', $config['schema']));
    }

    /**
     * @param array $config
     */
    protected function setEncoding(array $config): void
    {
        $config['charset'] = $config['charset'] ?? 'utf8';
        $this->exec("SET client_encoding TO {$this->quote(strtoupper($config['charset']))}");
    }

    /**
     * @param array $config
     */
    protected function setTimezone(array $config): void
    {
        $config['timezone'] = $config['timezone'] ?? 'UTC';
        $this->exec("SET timezone TO {$this->quote($config['timezone'])}");
    }
}
