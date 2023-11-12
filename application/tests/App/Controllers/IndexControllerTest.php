<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

namespace Tests\App\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class IndexControllerTest extends TestCase
{
    public function testIndex()
    {
        $response = $this->app->handle($this->createRequest('GET', '/'));
        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    public function testPageNotFound()
    {
        $response = $this->app->handle($this->createRequest('GET', '/not-found'));
        $this->assertSame(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
    }
}
