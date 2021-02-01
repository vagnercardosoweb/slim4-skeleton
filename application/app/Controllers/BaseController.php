<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/02/2021 Vagner Cardoso
 */

namespace App\Controllers;

use Core\Twig\Twig;
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
     * @var \Slim\Interfaces\RouteParserInterface
     */
    protected RouteParserInterface $routeParser;

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
        $this->routeParser = $container->get(RouteParserInterface::class);
    }

    /**
     * @return \Slim\Interfaces\RouteParserInterface
     */
    public function getRouteParser(): RouteParserInterface
    {
        return $this->routeParser;
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
     * @param string               $template
     * @param array<string, mixed> $context
     * @param int                  $status
     *
     * @return ResponseInterface
     */
    public function withView(
        string $template,
        array $context = [],
        int $status = StatusCodeInterface::STATUS_OK
    ): ResponseInterface {
        return $this->container
            ->get(Twig::class)
            ->render(
                $this->response,
                $template,
                $context,
                $status
            )
        ;
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
     * @param string               $routeName
     * @param array<string, mixed> $data
     * @param array<string, mixed> $queryParams
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function withRedirectFor(
        string $routeName,
        array $data = [],
        array $queryParams = []
    ): ResponseInterface {
        $destination = $this->getUrlFor($routeName, $data, $queryParams);

        return $this->withRedirect($destination);
    }

    /**
     * @param string               $routeName
     * @param array<string, mixed> $data
     * @param array<string, mixed> $queryParams
     *
     * @return string
     */
    public function getUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->urlFor($routeName, $data, $queryParams);
    }

    /**
     * @param string               $routeName
     * @param array<string, mixed> $data
     * @param array<string, mixed> $queryParams
     *
     * @return string
     */
    public function getFullUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        $uri = $this->request->getUri();

        return $this->routeParser->fullUrlFor($uri, $routeName, $data, $queryParams);
    }

    /**
     * @param string               $routeName
     * @param array<string, mixed> $data
     * @param array<string, mixed> $queryParams
     *
     * @return string
     */
    public function getRelativeUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->relativeUrlFor($routeName, $data, $queryParams);
    }

    /**
     * @param string               $destination
     * @param array<string, mixed> $queryParams
     * @param bool                 $permanent
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
