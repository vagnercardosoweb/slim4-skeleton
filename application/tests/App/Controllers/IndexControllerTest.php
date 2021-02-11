<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 11/02/2021 Vagner Cardoso
 */

namespace Tests\App\Controllers;

use Core\Bootstrap;
use Fig\Http\Message\StatusCodeInterface;
use Tests\TestCase;

/**
 * Class IndexControllerTest.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 *
 * @internal
 * @coversNothing
 */
class IndexControllerTest extends TestCase
{
    public function testIndex()
    {
        $request = $this->createRequest('GET', '/');
        $response = $this->app->handle($request);

        $this->assertJsonContentType($response);
        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertJsonValue(Bootstrap::VERSION, 'version', $response);
    }

    public function testTemplate()
    {
        $request = $this->createRequest('GET', '/template');
        $response = $this->app->handle($request);

        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertStringContainsString(sprintf('v%s', Bootstrap::VERSION), $response->getBody());
    }
}
