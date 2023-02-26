<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2023 Vagner Cardoso
 */

namespace App\Middlewares;

use Core\Support\Env;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $origin = Env::get('CORS_ORIGIN', '*');
        $methods = Env::get('CORS_METHODS', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $headers = Env::get('CORS_HEADERS', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');

        $response = $handler->handle($request)
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Allow-Methods', $methods)
            ->withHeader('Access-Control-Allow-Headers', $headers)
            ->withHeader('Access-Control-Allow-Credentials', 'true')
        ;

        if ('options' === strtolower($request->getMethod())) {
            $response = $response->withStatus(StatusCodeInterface::STATUS_OK);
        }

        return $response;
    }
}
