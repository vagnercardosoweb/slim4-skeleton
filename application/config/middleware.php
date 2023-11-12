<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 06/11/2023 Vagner Cardoso
 */

use App\Middlewares\CorsMiddleware;
use App\Middlewares\ErrorResponseMiddleware;
use App\Middlewares\MaintenanceMiddleware;
use App\Middlewares\NoCacheMiddleware;
use App\Middlewares\OldParsedBodyMiddleware;
use App\Middlewares\RequestIdMiddleware;
use App\Middlewares\TrailingSlashMiddleware;
use App\Middlewares\TranslatorMiddleware;
use Core\Handlers\HttpErrorHandler;
use Core\Handlers\ShutdownErrorHandler;
use Core\Support\Env;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;

return function (App $app) {
    $app->addRoutingMiddleware();
    $app->addBodyParsingMiddleware();

    $app->add(CorsMiddleware::class);
    $app->add(RequestIdMiddleware::class);
    $app->add(ContentLengthMiddleware::class);
    $app->add(MethodOverrideMiddleware::class);
    $app->add(NoCacheMiddleware::class);
    // $app->add(AuthorizationMiddleware::class);
    $app->add(OldParsedBodyMiddleware::class);
    $app->add(TrailingSlashMiddleware::class);
    $app->add(TranslatorMiddleware::class);
    $app->add(MaintenanceMiddleware::class);
    $app->add(ErrorResponseMiddleware::class);

    $logErrors = Env::get('SLIM_LOG_ERRORS', true);
    $logErrorDetails = Env::get('SLIM_LOG_ERROR_DETAIL', true);
    $displayErrorDetails = Env::get('SLIM_DISPLAY_ERROR_DETAILS', true);

    $logger = $app->getContainer()->get(LoggerInterface::class);
    $httpErrorHandler = new HttpErrorHandler($app->getCallableResolver(), $app->getResponseFactory(), $logger);
    $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);
    $errorMiddleware->setDefaultErrorHandler($httpErrorHandler);

    $serverRequest = $app->getContainer()->get(ServerRequestInterface::class);
    $shutdownErrorHandler = new ShutdownErrorHandler($serverRequest, $httpErrorHandler);
    register_shutdown_function($shutdownErrorHandler);
};
