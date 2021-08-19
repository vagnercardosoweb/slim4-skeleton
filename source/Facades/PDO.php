<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 19/08/2021 Vagner Cardoso
 */

namespace Core\Facades;

/**
 * Class PDO.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class PDO extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return \PDO::class;
    }
}
