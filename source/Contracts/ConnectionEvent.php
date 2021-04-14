<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 14/04/2021 Vagner Cardoso
 */

namespace Core\Contracts;

/**
 * Interface ConnectionEvent.
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
