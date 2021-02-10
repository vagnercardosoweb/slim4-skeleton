<?php

namespace Core\Contracts;

/**
 * Interface ConnectionEvent
 *
 * @package Core\Contracts
 */
interface ConnectionEvent
{
    /**
     * @param \PDO $pdo
     *
     * @return mixed
     */
    public function __invoke(\PDO $pdo): mixed;
}
