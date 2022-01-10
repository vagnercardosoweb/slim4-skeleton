<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 09/01/2022 Vagner Cardoso
 */

namespace Core;

use Slim\App;

/**
 * Class Module.
 */
abstract class Module
{
    /**
     * Module constructor.
     *
     * @param App $app
     */
    public function __construct(protected App $app)
    {
        $this->registerProviders();
        $this->registerMiddleware();
        $this->registerViews();
        $this->registerRoutes();
    }

    /**
     * @return void
     */
    abstract public function registerRoutes(): void;

    /**
     * @return void
     */
    abstract public function registerProviders(): void;

    /**
     * @return void
     */
    abstract public function registerMiddleware(): void;

    /**
     * @return void
     */
    abstract public function registerViews(): void;
}
