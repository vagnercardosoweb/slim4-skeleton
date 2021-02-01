<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 31/01/2021 Vagner Cardoso
 */

namespace App\Controllers;

use DI\Container;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteParserInterface;

/**
 * Class BaseController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class BaseController
{
    /**
     * BaseController constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     * @param \DI\Container                            $container
     */
    public function __construct(
        protected ServerRequestInterface $request,
        protected ResponseInterface $response,
        protected Container $container
    ) {
    }

    /**
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param array $data
     * @param int   $options
     *
     * @throws \Exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function withJson(
        $data = [],
        int $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ): ResponseInterface {
        $response = $this->response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR | $options));

        return $response;
    }

    /**
     * @param string $value
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function withString(string $value): ResponseInterface
    {
        $this->response->getBody()->write($value);

        return $this->response;
    }

    /**
     * @param string $routeName
     * @param array  $data
     * @param array  $queryParams
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function withRedirectFor(
        string $routeName,
        array $data = [],
        array $queryParams = []
    ): ResponseInterface {
        $destination = $this->pathFor($routeName, $data, $queryParams);

        return $this->withRedirect($destination);
    }

    /**
     * @param string $routeName
     * @param array  $data
     * @param array  $queryParams
     *
     * @return string
     */
    public function pathFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->container->get(RouteParserInterface::class)->urlFor($routeName, $data, $queryParams);
    }

    /**
     * @param string $destination
     * @param array  $queryParams
     * @param bool   $permanent
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function withRedirect(
        string $destination,
        array $queryParams = [],
        bool $permanent = false
    ): ResponseInterface {
        if ($queryParams) {
            $destination = sprintf('%s?%s', $destination, http_build_query($queryParams));
        }

        $statusCode = $permanent ? StatusCodeInterface::STATUS_MOVED_PERMANENTLY : StatusCodeInterface::STATUS_FOUND;

        return $this->response->withStatus($statusCode)->withHeader('Location', $destination);
    }
}
