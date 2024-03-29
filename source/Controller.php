<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

namespace Core;

use Core\Support\Common;
use Core\Twig\Twig;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteParserInterface;

abstract class Controller
{
    /**
     * @var RouteParserInterface
     */
    protected RouteParserInterface $routeParser;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param ContainerInterface     $container
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        protected readonly ServerRequestInterface $request,
        protected readonly ResponseInterface $response,
        protected readonly ContainerInterface $container
    ) {
        $this->routeParser = $container->get(RouteParserInterface::class);
    }

    /**
     * @return RouteParserInterface
     */
    public function getRouteParser(): RouteParserInterface
    {
        return $this->routeParser;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return ContainerInterface
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
     * @return ResponseInterface
     */
    public function withJson(
        array $data = [],
        int $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ): ResponseInterface {
        $response = $this->response->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR | $options));

        return $response;
    }

    public function isXhr(): bool
    {
        return 'XMLHttpRequest' === $this->request->getHeaderLine('X-Requested-With');
    }

    /**
     * @param string               $template
     * @param array<string, mixed> $context
     * @param int                  $status
     *
     * @return ResponseInterface
     */
    public function withTwig(
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
     * @param string               $template
     * @param array<string, mixed> $context
     *
     * @return string
     */
    public function twigFetch(string $template, array $context = []): string
    {
        return $this->container
            ->get(Twig::class)
            ->fetch(
                $template,
                $context
            )
        ;
    }

    /**
     * @param string $value
     *
     * @return ResponseInterface
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
     * @return ResponseInterface
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
     * @param string               $destination
     * @param array<string, mixed> $queryParams
     * @param bool                 $permanent
     *
     * @return ResponseInterface
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
     * @param array<string, mixed> $only
     *
     * @return array<string, mixed>
     */
    public function getQueryParams(array $only = []): array
    {
        $queryParams = $this->request->getQueryParams() ?? [];

        return $this->getRequestParams($queryParams, $only);
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $only
     *
     * @return array<string, mixed>
     */
    private function getRequestParams(array $params, array $only): array
    {
        if (!empty($only) && !empty($params)) {
            $params = array_intersect_key($params, array_flip($only));

            foreach ($only as $key) {
                $params[$key] = $params[$key] ?? null;
            }
        }

        return Common::filterValues($params);
    }

    /**
     * @param array<string, mixed> $only
     *
     * @return array<string, mixed>
     */
    public function getServerParams(array $only = []): array
    {
        $serverParams = $this->request->getServerParams() ?? [];

        return $this->getRequestParams($serverParams, $only);
    }

    /**
     * @param array<string, mixed> $only
     *
     * @return array<string, mixed>
     */
    public function getParsedBody(array $only = []): array
    {
        $parsedBody = $this->request->getParsedBody() ?? [];

        return $this->getRequestParams($parsedBody, $only);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if ($this->container->has($name)) {
            return $this->container->get($name);
        }

        return null;
    }
}
