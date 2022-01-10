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

use Psr\Http\Message\ResponseInterface;

trait AssertTestTrait
{
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
}
