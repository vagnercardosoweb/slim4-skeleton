<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 28/02/2023 Vagner Cardoso
 */

namespace App\Middlewares;

use Core\Facades\Encryption;
use Core\Facades\Jwt;
use Core\Support\Env;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpUnauthorizedException;

class AuthorizationMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $this->extractTokenInRequest($request);
        $request = $request->withAttribute('token', $token);

        if ($token === Env::get('API_SECRET_KEY')) {
            return $handler->handle($request);
        }

        if (empty($token)) {
            throw new HttpUnauthorizedException($request, 'Header with BEARER but without TOKEN.');
        }

        try {
            $parts = explode('.', $token);
            $decoded = 3 === count($parts) ? Jwt::decode($token) : Encryption::decrypt($token);
        } catch (Exception) {
            throw new HttpUnauthorizedException($request, 'Acesso negado, please login again.');
        }

        if ($decoded['exp'] && $decoded['exp'] < time()) {
            throw new HttpUnauthorizedException($request, 'Access expired, please login again.');
        }

        $request = $request->withAttribute('decodedToken', $decoded);

        return $handler->handle($request);
    }

    protected function extractTokenInRequest(ServerRequestInterface $request): string
    {
        $queryParams = $request->getQueryParams() ?? [];

        if (!empty($queryParams['token'])) {
            return trim($queryParams['token']);
        }

        $authorization = $request->getHeaderLine('Authorization');

        if (!empty($authorization)) {
            list(, $token) = explode(' ', $authorization) + [1 => ''];

            return trim($token);
        }

        return '';
    }
}
