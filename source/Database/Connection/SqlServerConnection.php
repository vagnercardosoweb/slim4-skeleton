<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/07/2021 Vagner Cardoso
 */

namespace Core\Database\Connection;

/**
 * Class SqlServerConnection.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class SqlServerConnection extends Connection
{
    /**
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config): string
    {
        if (in_array('sqlsrv', $this->getAvailableDrivers())) {
            return $this->getSqlSrvDsn($config);
        }

        return $this->getDblibDsn($config);
    }

    /**
     * @param array $config
     *
     * @return string
     */
    protected function getSqlSrvDsn(array $config): string
    {
        $arguments = [
            'Server' => $this->buildHostString($config, ','),
        ];

        if (isset($config['database'])) {
            $arguments['Database'] = $config['database'];
        }

        if (isset($config['readonly'])) {
            $arguments['ApplicationIntent'] = 'ReadOnly';
        }

        if (isset($config['pooling']) && false === $config['pooling']) {
            $arguments['ConnectionPooling'] = '0';
        }

        if (isset($config['appname'])) {
            $arguments['APP'] = $config['appname'];
        }

        if (isset($config['encrypt'])) {
            $arguments['Encrypt'] = $config['encrypt'];
        }

        if (isset($config['trust_server_certificate'])) {
            $arguments['TrustServerCertificate'] = $config['trust_server_certificate'];
        }

        if (isset($config['multiple_active_result_sets']) && false === $config['multiple_active_result_sets']) {
            $arguments['MultipleActiveResultSets'] = 'false';
        }

        if (isset($config['transaction_isolation'])) {
            $arguments['TransactionIsolation'] = $config['transaction_isolation'];
        }

        if (isset($config['multi_subnet_failover'])) {
            $arguments['MultiSubnetFailover'] = $config['multi_subnet_failover'];
        }

        return $this->buildConnectString('sqlsrv', $arguments);
    }

    /**
     * @param array  $config
     * @param string $separator
     *
     * @return string
     */
    protected function buildHostString(array $config, string $separator): string
    {
        if (isset($config['port']) && !empty($config['port'])) {
            return $config['host'].$separator.$config['port'];
        }

        return $config['host'];
    }

    /**
     * @param string $driver
     * @param array  $arguments
     *
     * @return string
     */
    protected function buildConnectString(string $driver, array $arguments): string
    {
        return $driver.':'.implode(';', array_map(function ($key) use ($arguments) {
            return sprintf('%s=%s', $key, $arguments[$key]);
        }, array_keys($arguments)));
    }

    /**
     * @param array $config
     *
     * @return string
     */
    protected function getDblibDsn(array $config): string
    {
        $arguments = [
            'host' => $this->buildHostString($config, ':'),
            'dbname' => $config['database'],
        ];

        if (isset($config['charset']) && 'utf8' == $config['charset']) {
            $arguments['charset'] = 'UTF-8';
            $arguments['version'] = '7.0';
        }

        if (isset($config['version'])) {
            $arguments['version'] = (string)$config['version'];
        }

        if (isset($config['version'])) {
            $arguments['version'] = (string)$config['version'];
        }

        if (isset($config['appname'])) {
            $arguments['appname'] = (string)$config['appname'];
        }

        return $this->buildConnectString('dblib', $arguments);
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
        if (!empty($config['charset']) && 'utf8' == $config['charset']) {
            $this->setAttribute(\PDO::SQLSRV_ATTR_ENCODING, \PDO::SQLSRV_ENCODING_UTF8);
        } else {
            $this->setAttribute(\PDO::SQLSRV_ATTR_ENCODING, \PDO::SQLSRV_ENCODING_DEFAULT);
        }
    }

    /**
     * @param array $config
     */
    protected function setTimezone(array $config): void
    {
        // TODO
    }
}
