<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 09/01/2022 Vagner Cardoso
 */

namespace Tests\App\Controllers;

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

        $this->assertHtmlContentType($response);
        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertStringContainsString('Você é capaz e sempre de o melhor de sí em tudo oque fizer.', $response->getBody());
    }

    public function testPageNotFound()
    {
        $request = $this->createRequest('GET', '/not-found');
        $response = $this->app->handle($request);

        $this->assertSame(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
    }
}
