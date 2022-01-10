<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 09/01/2022 Vagner Cardoso
 */

namespace Tests\Traits;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Slim\Psr7\Factory\ServerRequestFactory;

trait HttpTestTrait
{
    /**
     * Create a server request.
     *
     * @param string                                $method
     * @param \Psr\Http\Message\UriInterface|string $uri
     * @param array                                 $serverParams
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function createRequest(string $method, UriInterface | string $uri, array $serverParams = []): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest(
            strtoupper($method),
            $uri, $serverParams
        );
    }

    /**
     * Create a form request.
     *
     * @param string                                $method The HTTP method
     * @param string|\Psr\Http\Message\UriInterface $uri    The URI
     * @param array|null                            $data   The form data
     *
     * @return ServerRequestInterface
     */
    protected function createFormRequest(string $method, UriInterface | string $uri, array $data = null): ServerRequestInterface
    {
        $request = $this->createRequest($method, $uri);

        if (null !== $data) {
            $request = $request->withParsedBody($data);
        }

        return $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
    }

    /**
     * Create a new response.
     *
     * @param int    $code
     * @param string $reasonPhrase
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function createResponse(int $code = StatusCodeInterface::STATUS_OK, string $reasonPhrase = ''): ResponseInterface
    {
        $factory = $this->container->get(ResponseFactoryInterface::class);

        return $factory->createResponse($code, $reasonPhrase);
    }
}
