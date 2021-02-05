<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/02/2021 Vagner Cardoso
 */

namespace Tests;

use Core\Bootstrap;
use Core\Support\Arr;
use DI\Container;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * Class TestCase.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 *
 * @internal
 * @coversNothing
 */
class TestCase extends PHPUnitTestCase
{
    /**
     * @var \Slim\App
     */
    protected App $app;

    /**
     * @var \DI\Container|null
     */
    protected ?Container $container;

    /**
     * Starts settings.
     *
     * @return void
     */
    protected function setUp(): void
    {
        require_once __DIR__.'/../../public_html/index.php';

        $this->app = Bootstrap::getApp();
        $this->container = $this->app->getContainer();

        if (is_null($this->container)) {
            throw new \UnexpectedValueException(
                'Container is not an instance DI\\Container.'
            );
        }
    }

    /**
     * Add mock to container.
     *
     * @param string $class
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function mock(string $class): MockObject
    {
        if (!class_exists($class) && !interface_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class not found: %s', $class));
        }

        $mock = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock()
        ;

        $this->container->set($class, $mock);

        return $mock;
    }

    /**
     * Create a server request.
     *
     * @param string                                $method       The HTTP method
     * @param string|\Psr\Http\Message\UriInterface $uri          The URI
     * @param array<mixed>                          $serverParams The server parameters
     *
     * @return \Psr\Http\Message\ServerRequestInterface The request
     */
    protected function createRequest(string $method, UriInterface | string $uri, array $serverParams = []): ServerRequestInterface
    {
        $method = strtoupper($method);

        return (new ServerRequestFactory())->createServerRequest($method, $uri, $serverParams);
    }

    /**
     * Create a form request.
     *
     * @param string                                $method The HTTP method
     * @param string|\Psr\Http\Message\UriInterface $uri    The URI
     * @param array<mixed>|null                     $data   The form data
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
     * Create a JSON request.
     *
     * @param string              $method The HTTP method
     * @param string|UriInterface $uri    The URI
     * @param array<mixed>|null   $data   The json data
     *
     * @return ServerRequestInterface
     */
    protected function createJsonRequest(string $method, UriInterface | string $uri, array $data = null): ServerRequestInterface
    {
        $request = $this->createRequest($method, $uri);

        if (null !== $data) {
            $request = $request->withParsedBody($data);
        }

        return $request->withHeader('Content-Type', 'application/json');
    }

    /**
     * Get JSON response as array.
     *
     * @param ResponseInterface $response
     *
     * @return array The data
     */
    protected function getJsonData(ResponseInterface $response): array
    {
        $actual = (string)$response->getBody();
        $this->assertJson($actual);

        return (array)json_decode($actual, true);
    }

    /**
     * Verify that the specified array is an exact match for the returned JSON.
     *
     * @param array<mixed>      $expected The expected array
     * @param ResponseInterface $response The response
     *
     * @return void
     */
    protected function assertJsonData(array $expected, ResponseInterface $response): void
    {
        $this->assertSame($expected, $this->getJsonData($response));
    }

    /**
     * Verify JSON response.
     *
     * @param ResponseInterface $response The response
     *
     * @return void
     */
    protected function assertJsonContentType(ResponseInterface $response): void
    {
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Verify HTML response.
     *
     * @param ResponseInterface $response The response
     *
     * @return void
     */
    protected function assertHtmlContentType(ResponseInterface $response): void
    {
        $this->assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Verify that the specified array is an exact match for the returned JSON.
     *
     * @param mixed             $expected The expected value
     * @param string            $path     The array path
     * @param ResponseInterface $response The response
     *
     * @return void
     */
    protected function assertJsonValue(mixed $expected, string $path, ResponseInterface $response)
    {
        $this->assertSame($expected, Arr::get($this->getJsonData($response), $path));
    }
}
