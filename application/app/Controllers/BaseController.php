<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/01/2021 Vagner Cardoso
 */

namespace App\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;

/**
 * Class BaseController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class BaseController
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * BaseController constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array                               $data
     * @param int|string                          $options
     *
     * @throws \Exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function withJson(
        ResponseInterface $response,
        $data = [],
        int $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ): ResponseInterface {
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR | $options));

        return $response;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param string                              $destination
     * @param array                               $queryParams
     * @param bool                                $permanent
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function withRedirect(
        ResponseInterface $response,
        string $destination,
        array $queryParams = [],
        bool $permanent = false
    ): ResponseInterface {
        if ($queryParams) {
            $destination = sprintf('%s?%s', $destination, http_build_query($queryParams));
        }

        return $response->withStatus($permanent
            ? StatusCodeInterface::STATUS_MOVED_PERMANENTLY : StatusCodeInterface::STATUS_FOUND
        )
            ->withHeader('Location', $destination)
        ;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param string                              $routeName
     * @param array                               $data
     * @param array                               $queryParams
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function withRedirectFor(
        ResponseInterface $response,
        string $routeName,
        array $data = [],
        array $queryParams = []
    ): ResponseInterface {
        $destination = $this->container->get(RouteParserInterface::class)->urlFor($routeName, $data, $queryParams);

        return $this->withRedirect($response, $destination);
    }
}
