<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 06/11/2023 Vagner Cardoso
 */

namespace Core\Database\Connection;

/**
 * Class MySqlConnection.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class MySqlConnection extends Connection
{
    /**
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config): string
    {
        $config['port'] = $config['port'] ?? 3306;
        $config['application_name'] = $config['application_name'] ?? 'app';

        if (!empty($config['unix_socket'])) {
            return "mysql:unix_socket={$config['unix_socket']};dbname={$config['database']};options=--application_name={$config['application_name']}";
        }

        return "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}options=--application_name={$config['application_name']}";
    }

    /**
     * @param array $config
     */
    protected function setSchema(array $config): void
    {
        if (!empty($config['database'])) {
            $this->exec("USE {$config['database']};");
        }
    }

    /**
     * @param array $config
     */
    protected function setEncoding(array $config): void
    {
        if (!empty($config['charset'])) {
            $encoding = "SET NAMES {$this->quote($config['charset'])}";
            $encoding .= (!empty($config['collation']) ? " COLLATE {$this->quote($config['collation'])}" : '');
            $this->exec("{$encoding};");
        }
    }

    /**
     * @param array $config
     */
    protected function setTimezone(array $config): void
    {
        if (!empty($config['timezone'])) {
            $this->exec("SET time_zone = {$this->quote($config['timezone'])};");
        }
    }
}
