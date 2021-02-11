<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 11/02/2021 Vagner Cardoso
 */

namespace Core\Facades;

/**
 * Class App.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class App extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return \Slim\App::class;
    }
}
