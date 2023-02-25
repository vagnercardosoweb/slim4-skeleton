<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/02/2023 Vagner Cardoso
 */

namespace App\Middlewares;

use Core\Facades\Encryption;
use Core\Facades\Jwt;
use Core\Support\Env;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpUnauthorizedException;

/**
 * Class TokenMiddleware.
 */
class TokenMiddleware implements MiddlewareInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @throws \Exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $this->extractTokenInRequest($request);
        $request = $request->withAttribute('token', $token);

        if ($token === Env::get('API_SECRET_KEY')) {
            return $handler->handle($request);
        }

        try {
            try {
                $decodedToken = Jwt::decode($token);
            } catch (\Exception) {
                $decodedToken = Encryption::decrypt($token);
            }
        } catch (\Exception) {
            throw new HttpUnauthorizedException(
                $request,
                'Access denied, invalid token.'
            );
        }

        $expirationTime = $decodedToken['exp'] ?? $decodedToken['expired_at'] ?? null;

        if ($expirationTime && $expirationTime < time()) {
            throw new HttpUnauthorizedException(
                $request,
                'Access denied, expired token.'
            );
        }

        $request = $request->withAttribute('decodedToken', $decodedToken);

        return $handler->handle($request);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     */
    protected function extractTokenInRequest(ServerRequestInterface $request): string
    {
        $queryParams = $request->getQueryParams() ?? [];

        if (!empty($queryParams['token'])) {
            return trim($queryParams['token']);
        }

        $authorization = $request->getHeaderLine('Authorization');

        if (!empty($authorization)) {
            list(, $token) = explode(' ', $authorization);

            return trim($token);
        }

        return '';
    }
}
