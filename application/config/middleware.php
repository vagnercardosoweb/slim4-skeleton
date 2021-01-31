<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 31/01/2021 Vagner Cardoso
 */

use App\Middleware\CorsMiddleware;
use Core\Helpers\Env;
use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;

return function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->add(new MethodOverrideMiddleware());
    $app->add(new ContentLengthMiddleware());

    if (Env::get('ENABLE_CORS_ALL_ROUTES', false)) {
        $app->add(new CorsMiddleware());
    }

    $app->addRoutingMiddleware();
};
