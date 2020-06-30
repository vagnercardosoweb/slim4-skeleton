<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 30/06/2020 Vagner Cardoso
 */

namespace Core\Facades;

/**
 * Class App.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class App extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Slim\App::class;
    }
}
