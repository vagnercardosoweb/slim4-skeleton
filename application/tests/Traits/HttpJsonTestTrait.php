<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/02/2023 Vagner Cardoso
 */

namespace Tests\Traits;

use Core\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

trait HttpJsonTestTrait
{
    /**
     * Create a JSON request.
     *
     * @param string              $method The HTTP method
     * @param string|UriInterface $uri    The URI
     * @param array|null          $data   The json data
     *
     * @return ServerRequestInterface
     */
    protected function createJsonRequest(string $method, UriInterface|string $uri, array $data = null): ServerRequestInterface
    {
        $request = $this->createRequest($method, $uri);

        if (null !== $data) {
            $request = $request->withParsedBody($data);
        }

        return $request->withHeader('Content-Type', 'application/json');
    }

    /**
     * Verify that the specified array is an exact match for the returned JSON.
     *
     * @param array             $expected The expected array
     * @param ResponseInterface $response The response
     *
     * @return void
     */
    protected function assertJsonData(array $expected, ResponseInterface $response): void
    {
        $this->assertSame($expected, $this->getJsonData($response));
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
     * Verify that the specified array is an exact match for the returned JSON.
     *
     * @param mixed             $expected The expected value
     * @param string            $path     The array path
     * @param ResponseInterface $response The response
     *
     * @return void
     */
    protected function assertJsonValue(mixed $expected, string $path, ResponseInterface $response): void
    {
        $this->assertSame($expected, Arr::get($this->getJsonData($response), $path));
    }
}
