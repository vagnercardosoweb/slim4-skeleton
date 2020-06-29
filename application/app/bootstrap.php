<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 29/06/2020 Vagner Cardoso
 */

use Core\App;

require_once __DIR__.'/../vendor/autoload.php';

$app = new App();

$app->registerRoutes();

$app->run();
